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
                            <h2 class="text-xl font-bold">
                                {{ getFlagEmoji(player.country_code) }}
                                {{ props.player.name }} -
                                {{ props.player.rating }}
                                <a :href=generateProfileUrl(props.player.user_id) target="_blank">
                                    <p class="text-gray-600 font-mono underline font-light">
                                        {{ props.player.user_id }}
                                    </p>
                                </a>
                            </h2>
                        </span>
                    <button
                        @click="emitClose"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="h-64 mt-4 flex-grow overflow-hidden">
                    <div v-if="props.playerRatingHistory.length > 0" class="h-full">
                        <h3 class="text-lg font-semibold mb-2">Rating History (Last {{ daysToShow }} Days)</h3>
                        <div class="w-full h-52">
                            <canvas ref="ratingChartCanvas"></canvas>
                        </div>
                    </div>
                    <div v-else-if="props.isLoadingHistory" class="h-full flex flex-col justify-center items-center">
                        <div class="spinner-container flex justify-center items-center">
                            <div
                                class="spinner w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                        </div>
                        <p class="text-gray-500 mt-4">Loading rating history...</p>
                    </div>
                    <div
                        v-else
                        class="h-full flex flex-col justify-center items-center"
                    >
                        <svg class="h-16 w-16 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-lg font-semibold text-gray-700">:( We don't have any data for this
                            player!</p>
                        <p class="text-gray-500 mt-2">Check back later or try another player.</p>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import {usePlayerUtils} from "@composables/usePlayerUtils.js";
import {nextTick, onUnmounted, ref, watch} from "vue";
import {Chart} from "chart.js";

const emit = defineEmits(['close'])

const {getFlagEmoji, generateProfileUrl} = usePlayerUtils();

const ratingChartCanvas = ref(null);
const ratingChartInstance = ref(null);
const daysToShow = ref(7);

const handleKeydown = (e) => {
    if (e.key !== 'Escape') {
        return;
    }

    emitClose();
}

const emitClose = () => {
    emit('close');

    if (ratingChartInstance.value) {
        setTimeout(() => {
            ratingChartInstance.value.destroy();
            ratingChartInstance.value = null;
        }, 100);
    }
}

const props = defineProps({
    showModal: Boolean,
    player: Object,
    playerRatingHistory: Array,
    isLoadingHistory: Boolean,
})

const formatDateString = (date) => {
    return date.toISOString().split('T')[0];
};

const getDatesInRange = (startDate, endDate) => {
    const dates = [];
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

const calculateStepSize = (range) => {
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
            (a, b) => new Date(b.created_at) - new Date(a.created_at)
        );

        // in my head, we need the first rating *before* our accepted range. i.e. if we want the last
        // seven days of rating history, we want the first record that occurs outside that range (the first
        // record that is older than seven days) to fill any blank spaces between seven days ago and the first
        // record that appears in our data set. To do this, we sort this data from newest to oldest, then find
        // the first element that has as created_at timestamp before our start date variable...

        const leftOfStartDate = sortedRatingHistory.find(r => new Date(r.created_at) < startDate)
        const oldestRating = sortedRatingHistory[sortedRatingHistory.length - 1]

        const labels = [];
        const data = [];

        // ...then, we have a "hierarchy" of preferable data to fill in for the first potentially empty
        // days of data. The most preferable is the real rating the user had coming into the time frame
        // represented by this graph. If that doesn't exit though, we'll settle for the first element in
        // our list, aka the last element in our sorted list. Then, if that doesn't exist, that means we
        // have no rating changes to speak of and can fill in the whole graph with the player's current rating.

        let mostRecentRating = leftOfStartDate?.rating ?? oldestRating.rating ?? props.player.rating;

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

    if (data.length === 0) return;

    const validData = data.filter(value => value !== null);
    const minRating = Math.min(...validData);
    const maxRating = Math.max(...validData);

    const range = maxRating - minRating;
    const step = calculateStepSize(range)

    const buffer = step * 2;
    const yMin = Math.floor((minRating - buffer / 2) / step) * step;
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
                    boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                    titleFont: {
                        size: 13,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 12
                    },
                    callbacks: {
                        title: function (tooltipItems) {
                            return tooltipItems[0].label;
                        },
                        label: function (context) {
                            return `Rating: ${context.raw}`;
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
    async ([history, show]) => {
        if (show && props.playerRatingHistory) {
            await nextTick();
            renderRatingChart();
        }
    },
    {immediate: true}
);

watch(() => props.showModal, (show) => {
    if (show) {
        window.addEventListener('keydown', handleKeydown);
    } else {
        window.removeEventListener('keydown', handleKeydown);
    }
});

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
