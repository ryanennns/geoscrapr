<template>
    <transition name="fade">
        <div
            v-if="props.showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/75"
            @click.self="emitClose"
        >
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl" style="height: 360px;">
                <div class="flex justify-between items-start mb-4">
                    <span>
                        <span class="text-xl font-bold flex items-center">
                            <Flag
                                :country-code="player.country_code"
                                dimensions="24x18"
                                class="mr-1"
                            />
                            {{ props.player.name }} -
                            {{ props.player.rating }}
                        </span>
                        <a :href=generateProfileUrl(props.player.user_id) target="_blank">
                            <p class="text-gray-600 font-mono underline font-light">
                                {{ props.player.user_id }}
                            </p>
                        </a>
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
                    <LoadingSpinner v-show="props.loading" text="Loading rating history"/>
                    <div v-show="props.playerRatingHistory.length > 0 && !props.loading" class="h-full">
                        <h3 class="text-lg font-semibold mb-2">Rating History (Last {{ daysToShow }} Days)</h3>
                        <div class="w-full h-52">
                            <canvas ref="ratingChartCanvas"/>
                        </div>
                    </div>
                    <ErrorMessage
                        heading="We don't have any data for this player!"
                        subheading="Check back later or try another player."
                        v-show="props.playerRatingHistory.length < 1 && !props.loading"
                    />
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import {nextTick, onUnmounted, ref, watch} from "vue";
import {Chart, type TooltipItem} from "chart.js";
import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import Flag from "@/Components/Flag.vue";
import ErrorMessage from "@/Components/ErrorMessage.vue";
import {usePlayerUtils} from "@/composables/usePlayerUtils";
import type {Player, Rating} from "@/Types/core.ts";

const emit = defineEmits(['close'])

const {generateProfileUrl} = usePlayerUtils();

const ratingChartCanvas = ref<HTMLCanvasElement>();
const ratingChartInstance = ref<Chart | null>(null);
const daysToShow = ref(7);

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key !== 'Escape') {
        return;
    }

    emitClose();
}

const emitClose = () => {
    emit('close');

    if (ratingChartInstance.value) {
        setTimeout(() => {
            ratingChartInstance.value?.destroy();
            ratingChartInstance.value = null;
        }, 100);
    }
}

interface Props {
    showModal: boolean,
    player: Player,
    playerRatingHistory: Rating[],
    loading: boolean,
}

const props = defineProps<Props>()

const formatDateString = (date: Date) => {
    return date.toISOString().split('T')[0];
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
    if (!ratingChartCanvas.value || props.playerRatingHistory.length === 0) return;

    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
    }

    const ctx = ratingChartCanvas.value.getContext('2d');

    if (!ctx) {
        console.error('unable to get chart canvas context')

        return;
    }

    const processData = () => {
        const today = new Date();
        const startDate = new Date(today);
        startDate.setDate(today.getDate() - daysToShow.value);

        const allDates = getDatesInRange(startDate, today);

        const ratingsByDate = Object.fromEntries(
            props.playerRatingHistory.map(record => [
                formatDateString(new Date(record.created_at)),
                record.rating
            ])
        );

        const sortedRatingHistory = [...props.playerRatingHistory].sort(
            (a, b) => (new Date(b.created_at)).getTime() - (new Date(a.created_at)).getTime()
        );

        // in my head, we need the first rating *before* our accepted range. i.e. if we want the last
        // seven days of rating history, we want the first record that occurs outside that range (the first
        // record that is older than seven days) to fill any blank spaces between seven days ago and the first
        // record that appears in our data set. To do this, we sort this data from newest to oldest, then find
        // the first element that has as created_at timestamp before our start date variable...

        const leftOfStartDate = sortedRatingHistory.find(r => new Date(r.created_at) < startDate)
        const oldestRating = sortedRatingHistory[sortedRatingHistory.length - 1]

        const labels: string[] = [];
        const data: number[] = [];

        // ...then, we have a "hierarchy" of preferable data to fill in for the first potentially empty
        // days of data. The most preferable is the real rating the user had coming into the time frame
        // represented by this graph. If that doesn't exit though, we'll settle for the first element in
        // our list, aka the last element in our sorted list. Then, if that doesn't exist, that means we
        // have no rating changes to speak of and can fill in the whole graph with the player's current rating.

        let mostRecentRating = leftOfStartDate?.rating ?? oldestRating.rating ?? props.player?.rating;

        allDates.forEach(date => {
            const dateString = formatDateString(date);
            labels.push(date.toLocaleDateString());

            // then we can traverse our data set, checking if we have a rating for the current date. If so,
            // we'd rather append that -- so we update "mostRecentRating" with the correct value. Otherwise,
            // we continue to backfill this with the most recent rating.
            if (ratingsByDate[dateString]) {
                mostRecentRating = ratingsByDate[dateString]
            }

            data.push(mostRecentRating);
        });

        // I haven't geeked over a programming challenge like this in a long time.
        // Very satisfying when it worked! Shouts out to Radu C for being a valuable?
        // set for testing and also for being a GeoGuessr legend.
        return {labels, data};
    };

    const {labels, data} = processData();

    if (data.length === 0) {
        return;
    }

    const validData = data.filter(value => value !== null);
    const minRating = Math.min(...validData);
    const maxRating = Math.max(...validData);

    const range = maxRating - minRating;
    const step = calculateStepSize(range)

    const buffer = step * 2;
    const yMin = Math.max(0, Math.floor((minRating - buffer / 2) / step) * step);
    const yMax = Math.ceil((maxRating + buffer / 2) / step) * step;

    const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.05)');

    ratingChartInstance.value = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Rating',
                data: data,
                backgroundColor: gradient,
                borderColor: 'rgba(79, 70, 229, 0.9)',
                borderWidth: 2.5,
                tension: 0,
                fill: true,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: 'rgba(79, 70, 229, 1)',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: 'white',
                pointHoverBorderColor: 'rgba(79, 70, 229, 1)',
                pointHoverBorderWidth: 3,
                spanGaps: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
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
                            size: 11
                        },
                        padding: 8,
                        color: '#64748b'
                    },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.8)'
                    },
                    border: {
                        dash: [4, 4]
                    }
                },
                x: {
                    ticks: {
                        maxTicksLimit: Math.min(10, labels.length),
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10
                        },
                        padding: 5,
                        color: '#64748b'
                    },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#1e293b',
                    bodyColor: '#334155',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 6,
                    titleFont: {
                        size: 13,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 12
                    },
                    callbacks: {
                        title(tooltipItems: TooltipItem<'line'>[]) {
                            return tooltipItems[0].label;
                        },
                        label(context: TooltipItem<'line'>) {
                            return `Rating: ${(context.raw as number).toLocaleString()}`;
                        }
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });
};

watch(
    () => [props.playerRatingHistory, props.showModal, props.player],
    async ([_, show]) => {
        if (show && props.playerRatingHistory) {
            await nextTick();
            renderRatingChart();
        }
    },
    {immediate: true}
);

watch(() => props.showModal, (show) =>
    show ?
        window.addEventListener('keydown', handleKeydown) :
        window.removeEventListener('keydown', handleKeydown)
);

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
    }
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
