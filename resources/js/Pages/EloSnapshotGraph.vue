<!-- resources/js/Pages/EloSnapshotGraph.vue -->
<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">
            ELO Distribution ({{ (new Date(props.snapshot.date)).toDateString() }}) — Total Players: {{ snapshot.n }}
        </h1>
        <div class="w-full max-w-full h-[60vh]">
            <canvas ref="chartCanvas" class="w-full h-full"></canvas>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
    snapshot: Object,
})

const chartCanvas = ref(null)

onMounted(() => {
    const rawBuckets = props.snapshot.buckets

    const entries = Object.entries(rawBuckets)

    const lastNonZeroIndex = entries.map(([, value]) => value)
        .reduce((lastIndex, value, index) => value > 0 ? index : lastIndex, 0)

    const trimmedEntries = entries.slice(0, lastNonZeroIndex + 1)

    const labels = trimmedEntries.map(([key]) => key)
    const data = trimmedEntries.map(([, value]) => value)

    new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data,
                borderWidth: 1,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    enabled: true,
                },
                legend: {
                    display: false,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
        plugins: [{
            id: 'bar-value-labels',
            afterDatasetsDraw(chart) {
                const { ctx, data, chartArea: { top }, scales: { x, y } } = chart

                ctx.save()
                chart.data.datasets[0].data.forEach((value, i) => {
                    ctx.fillStyle = '#000'
                    ctx.font = '12px sans-serif'
                    ctx.textAlign = 'center'
                    ctx.fillText(
                        value,
                        x.getPixelForValue(i),
                        y.getPixelForValue(value) - 6
                    )
                })
                ctx.restore()
            }
        }]
    })

})

</script>
