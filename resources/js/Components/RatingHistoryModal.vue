<template>
    <transition name="fade">
        <div
            v-if="props.showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/75"
            @click.self="emitClose"
        >
            <div
                class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl"
                style="height: 360px"
            >
                <div class="flex justify-between items-start mb-4">
                    <span>
                        <span class="text-xl font-bold flex items-center">
                            <span
                                v-for="countryCode in props.leaderboardRow
                                    .countryCodes"
                            >
                                <Flag
                                    :country-code="countryCode"
                                    dimensions="120x90"
                                    class="mr-1"
                                    width="20"
                                    height="15"
                                />
                            </span>
                            <p class="">{{ props.leaderboardRow.name }}</p>
                            <p class="font-light ml-1">
                                - {{ props.leaderboardRow.rating }}
                            </p>
                        </span>

                        <span v-if="props.leaderboardRow.players" class="flex">
                            <a
                                :href="
                                    generateProfileUrl(
                                        props.leaderboardRow.players[0]
                                            ?.user_id,
                                    )
                                "
                                target="_blank"
                                class="mr-1"
                            >
                                <p
                                    class="text-gray-600 font-mono underline font-light"
                                >
                                    {{ props.leaderboardRow.players[0]?.name }}
                                </p>
                            </a>
                            &
                            <a
                                :href="
                                    generateProfileUrl(
                                        props.leaderboardRow.players[1]
                                            ?.user_id,
                                    )
                                "
                                target="_blank"
                                class="ml-1"
                            >
                                <p
                                    class="text-gray-600 font-mono underline font-light"
                                >
                                    {{ props.leaderboardRow.players[1]?.name }}
                                </p>
                            </a>
                        </span>
                        <span v-else>
                            <a
                                :href="
                                    generateProfileUrl(
                                        props.leaderboardRow.geoGuessrId,
                                    )
                                "
                                target="_blank"
                            >
                                <p
                                    class="text-gray-600 font-mono underline font-light"
                                >
                                    {{ props.leaderboardRow.id }}
                                </p>
                            </a>
                        </span>
                    </span>
                    <button
                        @click="emitClose"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div class="h-64 mt-4 flex-grow overflow-hidden">
                    <LoadingSpinner
                        v-show="props.loading"
                        text="Loading rating history"
                    />
                    <div
                        v-show="
                            props.ratingHistory.length > 0 && !props.loading
                        "
                        class="h-full"
                    >
                        <div class="w-full h-60">
                            <canvas ref="ratingChartCanvas" />
                        </div>
                    </div>
                    <ErrorMessage
                        heading="We don't have any data for this player!"
                        sub-heading="Check back later or try another player."
                        v-show="
                            props.ratingHistory.length < 1 && !props.loading
                        "
                    />
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { nextTick, onUnmounted, ref, watch } from "vue";
import { Chart, type TooltipItem } from "chart.js";
import Flag from "@/Components/Flag.vue";
import ErrorMessage from "@/Components/ErrorMessage.vue";
import { usePlayerUtils } from "@/composables/usePlayerUtils.ts";
import type { LeaderboardRow, RatingChange } from "@/Types/core.ts";
import LoadingSpinner from "@/Components/LoadingSpinner.vue";

interface Props {
    showModal: boolean;
    leaderboardRow: LeaderboardRow;
    ratingHistory: RatingChange[];
    loading: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits(["close"]);

const { generateProfileUrl } = usePlayerUtils();
const ratingChartCanvas = ref<HTMLCanvasElement>();
const ratingChartInstance = ref<Chart | null>(null);

const daysToShow = ref(14);

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key !== "Escape") {
        return;
    }

    emitClose();
};

const emitClose = () => {
    emit("close");

    if (ratingChartInstance.value) {
        setTimeout(() => {
            ratingChartInstance.value?.destroy();
            ratingChartInstance.value = null;
        }, 100);
    }
};

const formatDateString = (date: Date) => {
    return date.toISOString().split("T")[0];
};

const getDatesInRange = (startDate: Date, endDate: Date) => {
    const dates: Date[] = [];
    const currentDate = new Date(startDate);
    const endDateCopy = new Date(endDate);

    currentDate.setHours(0, 0, 0, 0);
    endDateCopy.setHours(0, 0, 0, 0);

    endDateCopy.setDate(endDateCopy.getDate() + 1);

    while (currentDate < endDateCopy) {
        dates.push(new Date(currentDate));
        currentDate.setDate(currentDate.getDate() + 1);
    }

    return dates;
};

const calculateStepSize = (range: number) => {
    if (range <= 100) return 50;
    if (range <= 200) return 100;
    if (range <= 400) return 250;
    return Math.ceil(range / 4 / 50) * 50;
};

const renderRatingChart = () => {
    if (!ratingChartCanvas.value || props.ratingHistory.length === 0) return;

    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
    }

    const ctx = ratingChartCanvas.value.getContext("2d");

    if (!ctx) {
        console.error("unable to get chart canvas context");

        return;
    }

    const processData = () => {
        const today = new Date();
        const startDate = new Date(today);
        startDate.setDate(today.getDate() - daysToShow.value);

        const allDates = getDatesInRange(startDate, today);

        const ratingsByDate = Object.fromEntries(
            props.ratingHistory.map((record) => [
                formatDateString(new Date(record.created_at)),
                record.rating,
            ]),
        );

        const sortedRatingHistory = [...props.ratingHistory].sort(
            (a, b) =>
                new Date(b.created_at).getTime() -
                new Date(a.created_at).getTime(),
        );

        const leftOfStartDate = sortedRatingHistory.find(
            (r) => new Date(r.created_at) < startDate,
        );
        const oldestRating =
            sortedRatingHistory[sortedRatingHistory.length - 1];

        const labels: string[] = [];
        const data: number[] = [];

        let mostRecentRating =
            leftOfStartDate?.rating ??
            oldestRating.rating ??
            props.leaderboardRow?.rating;

        allDates.forEach((date) => {
            const dateString = formatDateString(date);
            labels.push(date.toLocaleDateString());

            if (ratingsByDate[dateString]) {
                mostRecentRating = ratingsByDate[dateString];
            }

            data.push(mostRecentRating);
        });

        return { labels, data };
    };

    const { labels, data } = processData();

    if (data.length === 0) {
        return;
    }

    const validData = data.filter((value) => value !== null);
    const minRating = Math.min(...validData);
    const maxRating = Math.max(...validData);

    const range = maxRating - minRating;
    const step = calculateStepSize(range);

    const buffer = step * 2;
    const yMin = Math.max(
        0,
        Math.floor((minRating - buffer / 2) / step) * step,
    );
    const yMax = Math.ceil((maxRating + buffer / 2) / step) * step;

    const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
    gradient.addColorStop(0, "rgba(79, 70, 229, 0.4)");
    gradient.addColorStop(1, "rgba(79, 70, 229, 0.05)");

    ratingChartInstance.value = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Rating",
                    data: data,
                    backgroundColor: gradient,
                    borderColor: "rgba(79, 70, 229, 0.9)",
                    borderWidth: 2.5,
                    tension: 0,
                    fill: true,
                    pointBackgroundColor: "#ffffff",
                    pointBorderColor: "rgba(79, 70, 229, 1)",
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: "white",
                    pointHoverBorderColor: "rgba(79, 70, 229, 1)",
                    pointHoverBorderWidth: 3,
                    spanGaps: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: "index",
                intersect: false,
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: yMin,
                    max: yMax,
                    ticks: {
                        stepSize: step,
                        font: {
                            size: 11,
                        },
                        padding: 8,
                        color: "#64748b",
                    },
                    grid: {
                        color: "rgba(226, 232, 240, 0.8)",
                    },
                    border: {
                        dash: [4, 4],
                    },
                },
                x: {
                    title: {
                        display: true,
                        text: `Rating History (Last ${daysToShow.value} Days)`,
                        font: {
                            size: 12,
                        },
                        padding: {
                            top: 10,
                        },
                        color: "#1e293b",
                    },
                    ticks: {
                        maxTicksLimit: Math.min(10, labels.length),
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10,
                        },
                        padding: 5,
                        color: "#64748b",
                    },
                    grid: {
                        color: "rgba(226, 232, 240, 0.6)",
                    },
                },
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: "rgba(255, 255, 255, 0.95)",
                    titleColor: "#1e293b",
                    bodyColor: "#334155",
                    borderColor: "#e2e8f0",
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 6,
                    titleFont: {
                        size: 13,
                        weight: "bold",
                    },
                    bodyFont: {
                        size: 12,
                    },
                    callbacks: {
                        title(tooltipItems: TooltipItem<"line">[]) {
                            return tooltipItems[0].label;
                        },
                        label(context: TooltipItem<"line">) {
                            return `Rating: ${(context.raw as number).toLocaleString()}`;
                        },
                    },
                },
            },
            animation: {
                duration: 1500,
                easing: "easeOutQuart",
            },
        },
    });
};

watch(
    () => [props.ratingHistory, props.showModal, props.leaderboardRow],
    async ([_, show]) => {
        if (show && props.ratingHistory) {
            await nextTick();
            renderRatingChart();
        }
    },
    { immediate: true },
);

watch(
    () => props.showModal,
    (show) => {
        show
            ? window.addEventListener("keydown", handleKeydown)
            : window.removeEventListener("keydown", handleKeydown);
        show
            ? (document.documentElement.style.overflow = "hidden")
            : (document.documentElement.style.overflow = "auto");
    },
);

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeydown);
    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
    }
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
