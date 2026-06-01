<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from "vue";
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Title,
    Tooltip,
    Legend,
    Filler,
);

type ChartPoint = {
    x: number;
    y: number;
};

type ChartDataset = {
    label: string;
    data: ChartPoint[];
    tension?: number;
    borderWidth?: number;
    pointRadius?: number;
    fill?: boolean;
    borderColor?: string;
    backgroundColor?: string;
};

type RatingDistributionResponse = {
    labels: number[];
    datasets: ChartDataset[];
    summary: {
        bucket: string;
        mean_rating: number | null;
        median_rating: number | null;
    }[];
    meta: {
        rows: number;
        rating_min: number;
        rating_max: number;
        bucket_size: number;
        smoothing_window: number;
    };
};

const props = defineProps<{
    chartData: RatingDistributionResponse;
}>();

const lineColors = [
    "#0f766e",
    "#dc2626",
    "#2563eb",
    "#ca8a04",
    "#7c3aed",
];

const canvasRef = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

onMounted(() => {
    if (!canvasRef.value) {
        return;
    }

    chart = new Chart(canvasRef.value, {
        type: "line",
        data: {
            datasets: props.chartData.datasets.map((dataset, index) => ({
                ...dataset,
                borderColor: lineColors[index % lineColors.length],
                backgroundColor: lineColors[index % lineColors.length],
            })),
        },
        options: {
            parsing: false,
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: "nearest",
                intersect: false,
            },
            elements: {
                line: {
                    tension: 0.35,
                    borderWidth: 2.5,
                },
                point: {
                    radius: 0,
                    hitRadius: 8,
                },
            },
            scales: {
                x: {
                    type: "linear",
                    title: {
                        display: true,
                        text: props.chartData.meta.x_axis,
                    },
                    ticks: {
                        stepSize: 100,
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: props.chartData.meta.y_axis,
                    },
                    ticks: {
                        callback: (value) => `${value}%`,
                    },
                },
            },
            plugins: {
                legend: {
                    position: "top",
                    title: {
                        display: true,
                        text: "Games played",
                    },
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const label = context.dataset.label ?? "";
                            const y = Number(context.parsed.y).toFixed(2);

                            return `${label}: ${y}%`;
                        },
                    },
                },
            },
        },
    });
});

onBeforeUnmount(() => {
    chart?.destroy();
    chart = null;
});
</script>

<template>
    <section class="rating-distribution">
        <header class="rating-distribution__header">
            <h2>Rating Distribution by Games Played</h2>

            <p>
                {{ props.chartData.meta.rows.toLocaleString() }} players ·
                ratings {{ props.chartData.meta.rating_min }}–{{
                    props.chartData.meta.rating_max
                }}
                · smoothing window
                {{ props.chartData.meta.smoothing_window }}
            </p>
        </header>

        <div class="rating-distribution__chart">
            <canvas ref="canvasRef" />
        </div>

        <table class="rating-distribution__table">
            <thead>
                <tr>
                    <th>Bucket</th>
                    <th>Mean</th>
                    <th>Median</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="row in props.chartData.summary" :key="row.bucket">
                    <td>{{ row.bucket }}</td>
                    <td>{{ row.mean_rating ?? "—" }}</td>
                    <td>{{ row.median_rating ?? "—" }}</td>
                </tr>
            </tbody>
        </table>
    </section>
</template>

<style scoped>
.rating-distribution {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.rating-distribution__header h2 {
    margin: 0;
}

.rating-distribution__header p {
    margin: 4px 0 0;
    color: #666;
}

.rating-distribution__chart {
    height: 520px;
    width: 100%;
}

.rating-distribution__table {
    width: 100%;
    border-collapse: collapse;
}

.rating-distribution__table th,
.rating-distribution__table td {
    border-bottom: 1px solid #ddd;
    padding: 10px 12px;
    text-align: left;
}

.rating-distribution__table th {
    font-weight: 600;
}
</style>
