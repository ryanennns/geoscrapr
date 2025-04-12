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
                <p class="text-gray-500 mt-2">Please come back on a larger screen to explore the charts and stats.</p>
            </div>
        </div>
        <template v-else>
            <div class="p-8 bg-gray-50 min-h-screen">
                <div class="mb-8 text-center">
                    <h1 class="text-3xl font-bold text-indigo-800">GeoGuessr Competitive Rating Distribution</h1>
                    <p class="text-gray-600">Track player statistics and rating distributions</p>
                </div>

                <div class="flex justify-center items-center gap-4 mb-10 max-w-3xl mx-auto">
                    <PlayerSearch/>

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
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                        n = {{ currentSoloSnapshot?.n.toLocaleString() || 0 }}
                    </span>
                        </div>
                        <div class="w-full h-[60vh]">
                            <canvas ref="soloChartCanvas" class="w-full h-full"></canvas>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-md">
                        <div class="flex justify-between items-start mb-4">
                            <h2 class="text-2xl font-bold text-gray-800">Team Rating Distribution</h2>
                            <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                        n = {{ currentTeamSnapshot?.n.toLocaleString() || 0 }}
                    </span>
                        </div>
                        <div class="w-full h-[60vh]">
                            <canvas ref="teamChartCanvas" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import {ref, computed, onMounted} from 'vue'
import {Chart, registerables} from 'chart.js'
import '@vuepic/vue-datepicker/dist/main.css'
import PlayerSearch from "../Components/PlayerSearch.vue";
import DateSelector from "../Components/DateSelector.vue";
import {useRatingChart} from "@composables/useRatingChart.js";

const isSmallScreen = ref(false)

onMounted(() => {
    const checkSize = () => {
        isSmallScreen.value = window.innerWidth < 1300
    }

    checkSize()
    window.addEventListener('resize', checkSize)
})

Chart.defaults.animation = false
Chart.register(...registerables)

const props = defineProps({
    solo_snapshots: Array,
    team_snapshots: Array,
})

const soloChartCanvas = ref(null)
const teamChartCanvas = ref(null)

const soloChartInstance = ref(null)
const teamChartInstance = ref(null)

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
    return availableDates.value.map(parseLocalDate)
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

const currentSoloSnapshot = computed(() => {
    const formattedDate = formatDate(selectedDate.value)
    return props.solo_snapshots.find(s => s.date === formattedDate)
})

const currentTeamSnapshot = computed(() => {
    const formattedDate = formatDate(selectedDate.value)
    return props.team_snapshots.find(s => s.date === formattedDate)
})

const {renderChart} = useRatingChart();

const updateCharts = () => {
    renderChart(soloChartCanvas, currentSoloSnapshot.value, false, soloChartInstance)
    renderChart(teamChartCanvas, currentTeamSnapshot.value, true, teamChartInstance)
}

onMounted(updateCharts)
</script>
