<template>
    <div class="p-8 bg-gray-50 min-h-screen">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-indigo-800">GeoGuessr Competitive Rating Distribution</h1>
            <p class="text-gray-600">Track player statistics and rating distributions</p>
        </div>

        <div class="mb-10 w-full max-w-md mx-auto text-center">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Snapshot Date</label>
            <select v-model="selectedDate" @change="updateCharts"
                    class="text-sm border-gray-300 rounded w-full px-3 py-2">
                <option v-for="date in availableDates" :key="date" :value="date">
                    {{ new Date(date).toDateString() }}
                </option>
            </select>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Solo Chart -->
            <div class="bg-white p-6 rounded-xl shadow-md">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Solo Rating Distribution</h2>
                        <p class="text-sm text-gray-500">{{ new Date(selectedDate).toDateString() }}</p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                        n = {{ currentSoloSnapshot?.n || 0 }}
                    </span>
                </div>
                <div class="w-full h-[60vh]">
                    <canvas ref="soloChartCanvas" class="w-full h-full"></canvas>
                </div>
            </div>

            <!-- Team Chart -->
            <div class="bg-white p-6 rounded-xl shadow-md">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Team Rating Distribution</h2>
                        <p class="text-sm text-gray-500">{{ new Date(selectedDate).toDateString() }}</p>
                    </div>
                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                        n = {{ currentTeamSnapshot?.n || 0 }}
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
import {ref, computed, onMounted} from 'vue'
import {Chart, registerables} from 'chart.js'

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
    // Union of all unique dates across both sets
    const soloDates = props.solo_snapshots.map(s => s.date)
    const teamDates = props.team_snapshots.map(s => s.date)
    return [...new Set([...soloDates, ...teamDates])].sort().reverse()
})

const selectedDate = ref(availableDates.value[0])

const currentSoloSnapshot = computed(() =>
    props.solo_snapshots.find(s => s.date === selectedDate.value)
)
const currentTeamSnapshot = computed(() =>
    props.team_snapshots.find(s => s.date === selectedDate.value)
)

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
                    if (value > 0) {
                        ctx.fillStyle = '#292929'
                        ctx.font = 'bold 12px sans-serif'
                        ctx.textAlign = 'center'
                        ctx.fillText(value, x.getPixelForValue(i), y.getPixelForValue(value) - 8)
                    }
                })
                ctx.restore()
            },
        }],
    })
}

const updateCharts = () => {
    renderChart(soloChartCanvas, currentSoloSnapshot.value, false, soloChartInstance)
    renderChart(teamChartCanvas, currentTeamSnapshot.value, true, teamChartInstance)
}

onMounted(updateCharts)
</script>
