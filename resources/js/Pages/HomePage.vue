<template>
    <div class="p-8 bg-gray-50 min-h-screen">
        <div v-if="isSmallScreen" class="mt-20 text-center py-8">
            <div class="flex flex-col items-center">
                <svg class="h-16 w-16 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-lg font-semibold text-gray-700">This website is best experienced on desktop!</p>
                <p class="text-gray-500 mt-2">Please visit on a larger screen to explore the charts and stats.</p>
            </div>
        </div>
        <template v-else>
            <div class="mb-8 text-center ">
                <h1 class="text-3xl font-bold text-indigo-800">GeoGuessr Competitive Rating Distribution</h1>
                <p class="text-gray-600">Track player statistics and rating distributions</p>
            </div>

            <div class="flex justify-center items-center gap-4 mb-10 max-w-3xl mx-auto">
                <PlayerSearch
                    @player-click="onPlayerClick"
                />

                <DateSelector
                    v-model="selectedDate"
                    :availableDates="availableDatesObjects"
                    @update:model-value="updateCharts"
                />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Solo Rating Distribution</h2>
                        <Badge
                            :text="`n = ${currentSoloSnapshot?.n.toLocaleString() || 0}`"
                            class="bg-blue-100 text-blue-800"
                        />
                    </div>
                    <div class="w-full h-64 lg:h-[62vh]">
                        <canvas ref="soloChartCanvas" class="w-full h-full"></canvas>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Team Rating Distribution</h2>
                        <Badge
                            :text="`n = ${currentTeamSnapshot?.n.toLocaleString() || 0}`"
                            class="bg-green-100 text-green-800"
                        />
                    </div>
                    <div class="w-full h-64 lg:h-[62vh]">
                        <canvas ref="teamChartCanvas" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>

            <PlayerLeaderboard :players="props.leaderboard" @player-click="onPlayerClick" class="h-[93vh]"/>
            <transition name="fade">
                <RatingHistoryModal
                    :show-modal=showModal
                    :player=selectedPlayer
                    :player-rating-history=playerRatingHistory
                    :loading=isLoadingHistory
                    @close=closeModal
                />
            </transition>
        </template>
    </div>
</template>

<script setup>
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue'
import {Chart, registerables} from 'chart.js'
import '@vuepic/vue-datepicker/dist/main.css'
import PlayerSearch from "../Components/PlayerSearch.vue";
import DateSelector from "../Components/DateSelector.vue";
import {useRatingChart} from "@composables/useRatingChart.js";
import PlayerLeaderboard from "./PlayerLeaderboard.vue";
import RatingHistoryModal from "../Components/RatingHistoryModal.vue";
import Badge from "../Components/Badge.vue";

const isSmallScreen = ref(false)
const wasSmallScreen = ref(false)
const resizeTimer = ref(null)

// modal
const selectedPlayer = ref(null);
const showModal = ref(false);
const closeModal = () => showModal.value = false;
const playerRatingHistory = ref([]);
const isLoadingHistory = ref(false);

const onPlayerClick = async (payload) => {
    const player = payload.player
    selectedPlayer.value = player;
    setTimeout(() => showModal.value = true, 25);

    try {
        isLoadingHistory.value = true;
        const response = await fetch(`/players/history/${player.user_id}`);
        if (!response.ok) {
            throw new Error("Failed to fetch player details")
        }

        const historyData = await response.json();

        playerRatingHistory.value = historyData.sort(
            (a, b) => new Date(a.created_at) - new Date(b.created_at)
        );
    } catch (err) {
        console.error("Error loading player details:", err);
    } finally {
        setTimeout(() => isLoadingHistory.value = false, 150 + Math.random() * 150)
    }
}

const checkSize = () => {
    wasSmallScreen.value = isSmallScreen.value
    isSmallScreen.value =
        window.innerWidth < 768 ||
        window.innerHeight < 500 ||
        window.innerWidth / window.innerHeight < 0.8;

    if (resizeTimer.value) {
        clearTimeout(resizeTimer.value)
    }

    resizeTimer.value = setTimeout(() => {
        if (wasSmallScreen.value && !isSmallScreen.value) {
            setTimeout(initializeCharts, 100)
        }
    }, 250)
}

onMounted(() => {
    checkSize()
    window.addEventListener('resize', checkSize)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', checkSize)
    if (resizeTimer.value) {
        clearTimeout(resizeTimer.value)
    }
})

Chart.defaults.animation = false
Chart.register(...registerables)

const props = defineProps({
    solo_snapshots: Array,
    team_snapshots: Array,
    dates: Array,
    leaderboard: Array,
})

const soloChartCanvas = ref(null)
const teamChartCanvas = ref(null)

const soloChartInstance = ref(null)
const teamChartInstance = ref(null)

const soloSnapshots = ref(props.solo_snapshots);
const teamSnapshots = ref(props.team_snapshots);

const availableDates = computed(() => {
    const soloDates = props.solo_snapshots.map(s => s.date)
    const teamDates = props.team_snapshots.map(s => s.date)

    return [...new Set([...soloDates, ...teamDates])].sort().reverse()
})

function parseLocalDate(dateStr) {
    const [year, month, day] = dateStr.split('-').map(Number)
    return new Date(year, month - 1, day)
}

const availableDatesObjects = computed(() => {
    return props.dates.map(parseLocalDate)
})

const selectedDate = ref(
    availableDates.value[0] ? parseLocalDate(availableDates.value[0]) : new Date()
)

const formatDate = (date) => {
    if (!date) return null
    return date instanceof Date
        ? date.toISOString().split('T')[0]
        : date
}

const fetchSnapshot = async (date) => {
    const response = await fetch(`/snapshots?date=${formatDate(date)}`)

    if (!response.ok) {
        throw new Error(`Failed to fetch snapshot for ${date}.`)
    }

    return await response.json();
}

const currentSoloSnapshot = computed(() => {
    return soloSnapshots.value.find(s => s.date === formatDate(selectedDate.value))
})

const currentTeamSnapshot = computed(() => {
    return teamSnapshots.value.find(s => s.date === formatDate(selectedDate.value))
})

watch(selectedDate, async () => {
    if (
        soloSnapshots.value.find(s => s.date === formatDate(selectedDate.value)) &&
        teamSnapshots.value.find(s => s.date === formatDate(selectedDate.value))
    ) {
        return;
    }

    const snapshots = await fetchSnapshot(selectedDate.value)

    soloSnapshots.value.push(snapshots.solo)
    teamSnapshots.value.push(snapshots.team)
})

const {renderChart} = useRatingChart();

const updateCharts = () => {
    if (!isSmallScreen.value) {
        renderChart(soloChartCanvas, currentSoloSnapshot.value, false, soloChartInstance)
        renderChart(teamChartCanvas, currentTeamSnapshot.value, true, teamChartInstance)
    }
}

const initializeCharts = () => {
    if (soloChartInstance.value) {
        soloChartInstance.value.destroy()
        soloChartInstance.value = null
    }

    if (teamChartInstance.value) {
        teamChartInstance.value.destroy()
        teamChartInstance.value = null
    }

    updateCharts()
}

watch(isSmallScreen, (newValue, oldValue) => {
    if (!newValue && oldValue) {
        setTimeout(initializeCharts, 100)
    }
})

onMounted(() => {
    if (!isSmallScreen.value) {
        initializeCharts()
    }
})

watch([currentSoloSnapshot, currentTeamSnapshot, selectedDate], updateCharts)
</script>
