<template>
    <div class="p-8 bg-gray-50 min-h-screen">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-indigo-800">GeoGuessr Competitive Rating Distribution</h1>
            <p class="text-gray-600">Track player statistics and rating distributions</p>
        </div>

        <div class="flex justify-center items-center gap-4 mb-10 max-w-3xl mx-auto">
            <PlayerSearch />

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
import PlayerSearch from "../Components/PlayerSearch.vue";

// Chart.defaults.animation = false
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

    if (instanceRef.value) {
        instanceRef.value.destroy()
    }

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
