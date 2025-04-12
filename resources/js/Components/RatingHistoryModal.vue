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
                    <div v-if="props.playerRatingHistory.length > 1" class="h-full">
                        <h3 class="text-lg font-semibold mb-2">Rating History</h3>
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
                    <div v-else-if="props.playerRatingHistory.length <= 1" class="h-full flex flex-col justify-center items-center">
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

const emitClose = () => {
    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
        ratingChartInstance.value = null;
    }

    emit('close');
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

const calculateYAxisTicks = (minValue, maxValue) => {
    const standardIntervals = [10, 25, 50, 100, 200, 500, 1000];

    let interval = standardIntervals[0];

    const range = maxValue - minValue;

    const targetTickCount = 5;

    for (let i = 0; i < standardIntervals.length; i++) {
        if (range / standardIntervals[i] <= targetTickCount) {
            interval = standardIntervals[i];
            break;
        }
    }

    const minTick = Math.floor(minValue / interval) * interval - interval;
    const maxTick = Math.ceil(maxValue / interval) * interval + interval;

    const ticks = [];
    for (let tick = minTick; tick <= maxTick; tick += interval) {
        ticks.push(tick);
    }

    return {
        min: minTick,
        max: maxTick,
        interval: interval,
        ticks: ticks
    };
};

const renderRatingChart = () => {
    if (!ratingChartCanvas.value || props.playerRatingHistory.length <= 1) return;

    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
    }

    const ctx = ratingChartCanvas.value.getContext('2d');

    const processData = () => {
        const ratingsByDate = {};

        props.playerRatingHistory.forEach(record => {
            const date = new Date(record.created_at);
            const dateString = formatDateString(date);
            ratingsByDate[dateString] = record.rating;
        });

        const firstRecord = props.playerRatingHistory[0];
        const lastRecord = props.playerRatingHistory[props.playerRatingHistory.length - 1];

        const firstDate = new Date(firstRecord.created_at);
        const lastDate = new Date(lastRecord.created_at);

        const allDates = getDatesInRange(firstDate, lastDate);

        const labels = [];
        const data = [];

        allDates.forEach(date => {
            const dateString = formatDateString(date);
            labels.push(date.toLocaleDateString());

            data.push(ratingsByDate[dateString] || null);
        });

        return {labels, data};
    };

    const {labels, data} = processData();

    const validData = data.filter(value => value !== null);
    const minRating = Math.min(...validData);
    const maxRating = Math.max(...validData);

    const yAxis = calculateYAxisTicks(minRating, maxRating);

    ratingChartInstance.value = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Rating',
                data: data,
                backgroundColor: 'rgba(79, 70, 229, 0.2)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 2,
                tension: 0.1,
                pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                pointRadius: 4,
                pointHoverRadius: 6,
                spanGaps: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    min: yAxis.min,
                    max: yAxis.max,
                    ticks: {
                        callback: function(value) {
                            return yAxis.ticks.includes(value) ? value : null;
                        },
                        stepSize: yAxis.interval
                    },
                    title: {
                        display: true,
                    }
                },
                x: {
                    title: {
                        display: true,
                    },
                    ticks: {
                        maxTicksLimit: 10
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.raw !== null
                                ? `Rating: ${context.raw}`
                                : 'No data for this date';
                        }
                    }
                }
            }
        }
    });
};

watch(
    () => [props.playerRatingHistory, props.showModal],
    async ([history, show]) => {
        if (show && history.length > 1) {
            await nextTick();
            renderRatingChart();
        }
    },
    {immediate: true}
);

onUnmounted(() => {
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
