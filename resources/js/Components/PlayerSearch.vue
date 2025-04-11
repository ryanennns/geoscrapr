<template>
    <div class="relative w-full max-w-md">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                     fill="currentColor">
                    <path fill-rule="evenodd"
                          d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                          clip-rule="evenodd"/>
                </svg>
            </div>
            <input
                type="text"
                v-model="searchQuery"
                @input="fetchPlayers"
                placeholder="Search for a player name or ID..."
                class="pl-10 pr-10 py-3 border border-gray-300 rounded-lg shadow-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
            />
            <transition name="fade">
                <button
                    v-if="searchQuery"
                    @click="() => { searchQuery = ''; searchResults = []; }"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </transition>
        </div>

        <ul
            v-if="searchResults.length"
            class="absolute top-full left-0 z-10 bg-gray-50 w-full shadow-md max-h-64 overflow-y-auto rounded-lg mt-1"
        >
            <li
                v-for="player in searchResults"
                :key="player.id"
                @click="handlePlayerClick(player)"
                class="py-3 px-4 hover:bg-indigo-50 cursor-pointer transition-colors"
            >
                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-800">
                      {{ getFlagEmoji(player.country_code) }} {{ player.name }}
                    </span>
                    <span
                        class="flex items-center justify-center bg-indigo-100 text-indigo-800 py-1 px-2 rounded-full text-sm font-semibold w-26 h-full">
                        Rating: {{ player.rating }}
                    </span>
                </div>
            </li>
        </ul>

        <transition name="fade">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/75"
                @click.self="showModal = false"
            >
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-bold">{{ getFlagEmoji(selectedPlayer.country_code) }}
                                {{ selectedPlayer.name }} -
                                {{ selectedPlayer.rating }}</h2>
                            <p class="text-gray-600 font-mono">{{ selectedPlayer.id }}</p>
                        </div>
                        <button
                            @click="showModal = false"
                            class="text-gray-500 hover:text-gray-700"
                        >
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div v-if="playerRatingHistory.length > 1" class="mt-4">
                        <h3 class="text-lg font-semibold mb-2">Rating History</h3>
                        <div class="w-full h-64">
                            <canvas ref="ratingChartCanvas"></canvas>
                        </div>
                    </div>
                    <div v-else-if="isLoadingHistory" class="mt-4 text-center py-8">
                        <div class="spinner-container flex justify-center items-center py-10">
                            <div
                                class="spinner w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                        </div>
                        <p class="text-gray-500 mt-4">Loading rating history...</p>
                    </div>
                    <div v-else-if="playerRatingHistory.length <= 1" class="mt-4 text-center py-8">
                        <div class="flex flex-col items-center">
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

                    <div class="mt-6 flex justify-end">
                        <button
                            @click="showModal = false"
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import {ref, watch, onMounted, onUnmounted} from "vue";
import {Chart, registerables} from "chart.js";

Chart.register(...registerables);

const searchQuery = ref('');
const searchResults = ref([]);
const selectedPlayer = ref(null);
const showModal = ref(false);
const playerRatingHistory = ref([]);
const isLoadingHistory = ref(false);
const ratingChartCanvas = ref(null);
const ratingChartInstance = ref(null);

let debounceTimeout = null;
const fetchPlayers = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(async () => {
        if (searchQuery.value.length < 2) {
            searchResults.value = [];
            return;
        }

        try {
            const response = await fetch(`/players/search?q=${encodeURIComponent(searchQuery.value)}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            searchResults.value = await response.json();
        } catch (err) {
            console.error('Player search failed:', err);
        }
    }, 300);
};

watch(searchQuery, () => {
    fetchPlayers();
});

watch(showModal, (newValue) => {
    if (!newValue && ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
        ratingChartInstance.value = null;
    }
});

const getFlagEmoji = (countryCode) => {
    if (!countryCode || countryCode.length !== 2) {
        return '🏴';
    }

    try {
        return String.fromCodePoint(
            ...countryCode
                .toUpperCase()
                .split('')
                .map(char => 127397 + char.charCodeAt())
        );
    } catch {
        return '🏴';
    }
};

// Helper function to create a date string in YYYY-MM-DD format
const formatDateString = (date) => {
    return date.toISOString().split('T')[0];
};

// Get all dates between start and end dates as an array
const getDatesInRange = (startDate, endDate) => {
    const dates = [];
    const currentDate = new Date(startDate);

    // Create a copy of the end date to avoid modifying the original
    const endDateCopy = new Date(endDate);

    // Set both dates to midnight to ensure we're comparing just the dates
    currentDate.setHours(0, 0, 0, 0);
    endDateCopy.setHours(0, 0, 0, 0);

    // Add one day to make sure we include the end date
    endDateCopy.setDate(endDateCopy.getDate() + 1);

    while (currentDate < endDateCopy) {
        dates.push(new Date(currentDate));
        currentDate.setDate(currentDate.getDate() + 1);
    }

    return dates;
};

const handlePlayerClick = async (player) => {
    selectedPlayer.value = player;
    showModal.value = true;
    playerRatingHistory.value = [];
    isLoadingHistory.value = true;

    // Record the start time
    const startTime = Date.now();

    try {
        const res = await fetch(`/players/history/${player.user_id}`);
        if (!res.ok) throw new Error("Failed to fetch player details");

        const historyData = await res.json();

        // Sort the data by date
        playerRatingHistory.value = historyData.sort((a, b) =>
            new Date(a.created_at) - new Date(b.created_at)
        );

        // Calculate how much time has passed
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, 300 - elapsedTime);

        // If less than 150ms have passed, wait for the remaining time
        if (remainingTime > 0) {
            await new Promise(resolve => setTimeout(resolve, remainingTime));
        }

        if (playerRatingHistory.value.length > 1) {
            setTimeout(() => {
                renderRatingChart();
            }, 100);
        }
    } catch (err) {
        console.error("Error loading player details:", err);

        // Also ensure minimum loading time for error cases
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, 150 - elapsedTime);

        if (remainingTime > 0) {
            await new Promise(resolve => setTimeout(resolve, remainingTime));
        }
    } finally {
        isLoadingHistory.value = false;
    }
};

const renderRatingChart = () => {
    if (!ratingChartCanvas.value || playerRatingHistory.value.length <= 1) return;

    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
    }

    const ctx = ratingChartCanvas.value.getContext('2d');

    // Process data for chart display with daily intervals
    const processData = () => {
        // Create a map to easily look up ratings by date
        const ratingsByDate = {};

        // Convert dates to YYYY-MM-DD format for the map
        playerRatingHistory.value.forEach(record => {
            const date = new Date(record.created_at);
            const dateString = formatDateString(date);
            ratingsByDate[dateString] = record.rating;
        });

        // Determine first and last dates in the history
        const firstRecord = playerRatingHistory.value[0];
        const lastRecord = playerRatingHistory.value[playerRatingHistory.value.length - 1];

        const firstDate = new Date(firstRecord.created_at);
        const lastDate = new Date(lastRecord.created_at);

        // Get all dates between first and last
        const allDates = getDatesInRange(firstDate, lastDate);

        // Create dataset with all dates, with null values for dates without ratings
        const labels = [];
        const data = [];

        allDates.forEach(date => {
            const dateString = formatDateString(date);
            labels.push(date.toLocaleDateString());

            // Use the rating if it exists for this date, otherwise null
            data.push(ratingsByDate[dateString] || null);
        });

        return {labels, data};
    };

    const {labels, data} = processData();

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
                // Connect lines across null values
                spanGaps: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Rating'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    },
                    ticks: {
                        // Limit visible labels if there are too many
                        maxTicksLimit: 10
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Rating History',
                    font: {
                        size: 16
                    }
                },
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

onUnmounted(() => {
    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
    }
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
