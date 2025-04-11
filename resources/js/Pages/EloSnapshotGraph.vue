<template>
    <div class="p-8 bg-gray-50 min-h-screen">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-indigo-800">GeoGuessr Competitive Rating Distribution</h1>
            <p class="text-gray-600">Track player statistics and rating distributions</p>
        </div>

        <div class="flex justify-center items-center gap-4 mb-10 max-w-3xl mx-auto">
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
                        placeholder="Search for a player..."
                        class="pl-10 pr-10 py-3 border border-gray-300 rounded-lg shadow-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                    />
                    <transition name="fade">
                        <button
                            v-if="searchQuery"
                            @click="() => { searchQuery = ''; fetchPlayers(); }"
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
                    v-if="results.length"
                    class="absolute top-full left-0 z-10 bg-gray-50 border border-gray-200 w-full shadow-md max-h-64 overflow-y-auto rounded-lg mt-1"
                >
                    <li
                        v-for="player in results"
                        :key="player.id"
                        class="py-3 px-4 hover:bg-indigo-50 cursor-pointer border-b last:border-b-0 transition-colors"
                    >
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-800">{{ player.name }}</span>
                            <span
                                class="items-center bg-indigo-100 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-100 py-1 px-2 rounded-full text-sm font-semibold w-26">
                    Rating: {{ player.rating }}
                </span>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="w-40">
                <div class="relative">
                    <Datepicker
                        v-model="selectedDate"
                        :enable-time-picker="false"
                        :allowed-dates="availableDatesObjects"
                        @update:model-value="updateCharts"
                        placeholder="Pick a date"
                        auto-apply
                        clearable
                    />
                </div>
            </div>
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

<script setup>
import {ref, computed, onMounted, watch} from 'vue'
import {Chart, registerables} from 'chart.js'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

const searchQuery = ref('')
const results = ref([])

let debounceTimeout = null
const fetchPlayers = () => {
    clearTimeout(debounceTimeout)
    debounceTimeout = setTimeout(async () => {
        if (searchQuery.value.length < 2) {
            results.value = []
            return
        }

        try {
            const response = await fetch(`/players/search?q=${encodeURIComponent(searchQuery.value)}`)
            if (!response.ok) throw new Error('Network response was not ok')
            results.value = await response.json()
        } catch (err) {
            console.error('Player search failed:', err)
        }
    }, 300)
}

watch(() => searchQuery, () => {
    fetchPlayers();
});

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

    console.log('awooga', [...soloDates, ...teamDates])

    return [...new Set([...soloDates, ...teamDates])].sort().reverse()
})

function parseLocalDate(dateStr) {
    const [year, month, day] = dateStr.split('-').map(Number)
    return new Date(year, month - 1, day) // this is local midnight
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
    console.log('awooga', formatDate(selectedDate.value));

    const formattedDate = formatDate(selectedDate.value)
    return props.solo_snapshots.find(function (s) {
        console.log('awooga', s.date, formattedDate, s.date === formattedDate)
        return s.date === formattedDate;
    })
})

const currentTeamSnapshot = computed(() => {
    const formattedDate = formatDate(selectedDate.value)
    return props.team_snapshots.find(s => s.date === formattedDate)
})

const renderChart = (canvasRef, snapshot, isTeamChart = false, instanceRef) => {
    if (!snapshot) return

    const entries = Object.entries(snapshot.buckets)
    const lastNonZeroIndex = entries.map(([, v]) => v)
        .reduce((lastIdx, val, idx) => val > 0 ? idx : lastIdx, 0)

    const trimmed = entries.slice(0, lastNonZeroIndex + 1)
    const labels = trimmed.map(([key]) => key)
    const data = trimmed.map(([, val]) => val)

    const numericLabels = labels.map(label => parseInt(label.match(/\d+/)?.[0] || '0', 10))
    const min = Math.min(...numericLabels)
    const max = Math.max(...numericLabels)

    const backgroundColors = numericLabels.map(r => {
        const norm = (r - min) / (max - min || 1)
        return isTeamChart
            ? `rgba(${110 - norm * 70}, ${231 - norm * 50}, ${183 + norm * 30}, 0.75)`
            : `rgba(${147 + norm * 100}, ${197 - norm * 160}, ${253 - norm * 25}, 0.75)`
    })
    const borderColors = backgroundColors.map(c => c.replace('0.75', '1.0'))

    if (instanceRef.value) instanceRef.value.destroy()

    instanceRef.value = new Chart(canvasRef.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1,
                borderRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    titleFont: {size: 14, weight: 'bold'},
                    bodyFont: {size: 13},
                    callbacks: {
                        title: items => `Rating Range: ${items[0].label}`,
                        label: ctx => `Players: ${ctx.raw}`,
                    }
                },
                legend: {display: false},
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {color: 'rgba(0,0,0,0.05)'},
                    ticks: {font: {size: 12}},
                },
                x: {
                    grid: {display: false},
                    ticks: {
                        font: {size: 11},
                        maxRotation: 45,
                        minRotation: 45,
                    },
                },
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart',
            },
        },
        plugins: [{
            id: 'bar-value-labels',
            afterDatasetsDraw(chart) {
                const {ctx, scales: {x, y}} = chart
                ctx.save()
                chart.data.datasets[0].data.forEach((value, i) => {
                    ctx.fillStyle = '#292929'
                    ctx.font = 'bold 12px sans-serif'
                    ctx.textAlign = 'center'
                    ctx.fillText(value.toLocaleString(), x.getPixelForValue(i), y.getPixelForValue(value) - 8)
                })
                ctx.restore()
            },
        }],
    })
}

let lastTrigger = 0
const debounceDelay = 500
let timeoutHandle = null

const updateCharts = () => {
    const now = Date.now()

    if (now - lastTrigger > debounceDelay) {
        // fire immediately
        lastTrigger = now
        renderChart(soloChartCanvas, currentSoloSnapshot.value, false, soloChartInstance)
        renderChart(teamChartCanvas, currentTeamSnapshot.value, true, teamChartInstance)

        // reset lock after 500ms
        clearTimeout(timeoutHandle)
        timeoutHandle = setTimeout(() => {
            lastTrigger = 0
        }, debounceDelay)
    }
}

onMounted(updateCharts)
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
</style>
