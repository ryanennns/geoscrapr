<template>
    <div class="p-8 bg-gray-50 min-h-screen">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-indigo-800">GeoGuessr Competitive Rating Distribution</h1>
            <p class="text-gray-600">Track player statistics and rating distributions</p>
        </div>

        <div class="relative mb-10 w-full max-w-md mx-auto">
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
                    class="pl-10 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                />
            </div>

            <ul
                v-if="results.length"
                class="absolute top-full left-0 z-10 bg-white border border-gray-200 w-full shadow-lg max-h-64 overflow-y-auto rounded-lg mt-1"
            >
                <li
                    v-for="player in results"
                    :key="player.id"
                    class="py-3 px-4 hover:bg-indigo-50 cursor-pointer border-b last:border-b-0 transition-colors"
                >
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-800">{{ player.name }}</span>
                        <span class="bg-indigo-100 text-indigo-800 py-1 px-2 rounded-full text-sm font-semibold">Rating: {{
                                player.rating
                            }}</span>
                    </div>
                </li>
            </ul>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-xl shadow-md">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Solo Rating Distribution</h2>
                        <p class="text-sm text-gray-500">{{ (new Date(props.solo_snapshot.date)).toDateString() }}</p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">n = {{
                            props.solo_snapshot.n
                        }}</span>
                </div>
                <div class="w-full h-[60vh]">
                    <canvas ref="soloChartCanvas" class="w-full h-full"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Team Rating Distribution</h2>
                        <p class="text-sm text-gray-500">{{ (new Date(props.team_snapshot.date)).toDateString() }}</p>
                    </div>
                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">n = {{
                            props.team_snapshot.n
                        }}</span>
                </div>
                <div class="w-full h-[60vh]">
                    <canvas ref="teamChartCanvas" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {onMounted, ref} from 'vue'
import {Chart, registerables} from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
    solo_snapshot: Object,
    team_snapshot: Object,
})

const soloChartCanvas = ref(null)
const teamChartCanvas = ref(null)

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

onMounted(() => {
    const renderChart = (canvasRef, snapshot, isTeamChart = false) => {
        const rawBuckets = snapshot.buckets
        const entries = Object.entries(rawBuckets)

        const lastNonZeroIndex = entries.map(([, value]) => value)
            .reduce((lastIndex, value, index) => value > 0 ? index : lastIndex, 0)

        const trimmedEntries = entries.slice(0, lastNonZeroIndex + 1)
        const labels = trimmedEntries.map(([key]) => key)
        const data = trimmedEntries.map(([, value]) => value)

        const ctx = canvasRef.value.getContext('2d')

        const numericLabels = labels.map(label => {
            const match = label.match(/\d+/)
            return match ? parseInt(match[0], 10) : 0
        })

        const minRating = Math.min(...numericLabels)
        const maxRating = Math.max(...numericLabels)

        const backgroundColors = numericLabels.map(rating => {
            const normalizedRating = (rating - minRating) / (maxRating - minRating || 1)

            if (isTeamChart) {
                const r = Math.round(110 - (normalizedRating * 70))
                const g = Math.round(231 - (normalizedRating * 50))
                const b = Math.round(183 + (normalizedRating * 30))
                return `rgba(${r}, ${g}, ${b}, 0.75)`
            } else {
                const r = Math.round(147 + (normalizedRating * 100))
                const g = Math.round(197 - (normalizedRating * 160))
                const b = Math.round(253 - (normalizedRating * 25))
                return `rgba(${r}, ${g}, ${b}, 0.75)`
            }
        })

        const borderColors = backgroundColors.map(color => {
            return color.replace('0.75', '1.0')
        })

        new Chart(canvasRef.value, {
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
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                        },
                        bodyFont: {
                            size: 13,
                        },
                        callbacks: {
                            title: (tooltipItems) => {
                                return `Rating Range: ${tooltipItems[0].label}`;
                            },
                            label: (context) => {
                                return `Players: ${context.raw}`;
                            }
                        }
                    },
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                        },
                        ticks: {
                            font: {
                                size: 12,
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 11,
                            },
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
                    const {ctx, chartArea: {top}, scales: {x, y}} = chart

                    ctx.save()
                    chart.data.datasets[0].data.forEach((value, i) => {
                        console.log('elo', JSON.stringify({value}));
                        if (value > 0) {
                            // const bgColor = chart.data.datasets[0].backgroundColor[i]
                            // const rgbMatch = bgColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/)
                            // if (rgbMatch) {
                            // } else {
                            //     ctx.fillStyle = '#000000'
                            // }
                            ctx.fillStyle = '#292929'
                            ctx.font = 'bold 12px sans-serif'
                            ctx.textAlign = 'center'
                            ctx.fillText(
                                value,
                                x.getPixelForValue(i),
                                y.getPixelForValue(value) - 8
                            )
                        }
                    })
                    ctx.restore()
                },
            }],
        })
    }

    renderChart(soloChartCanvas, props.solo_snapshot, false)
    renderChart(teamChartCanvas, props.team_snapshot, true)
})
</script>
