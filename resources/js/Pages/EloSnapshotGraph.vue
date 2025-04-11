<template>
    <div class="p-6 m-4">
        <div class="relative mb-8 w-full max-w-md">
            <input
                type="text"
                v-model="searchQuery"
                @input="fetchPlayers"
                placeholder="Search for a player..."
                class="p-2 border rounded w-full"
            />
            <ul
                v-if="results.length"
                class="absolute top-full left-0 z-10 bg-white border border-gray-300 w-full shadow-lg max-h-64 overflow-y-auto"
            >
                <li
                    v-for="player in results"
                    :key="player.id"
                    class="py-2 px-4 hover:bg-gray-100 cursor-pointer border-b"
                >
                    {{ player.name }} — ELO: {{ player.rating }}
                </li>
            </ul>
        </div>
        <div class="flex flex-row">
            <div class="mr-2">
                <h1 class="text-2xl font-bold">
                    Solo Elo Distribution ({{ (new Date(props.solo_snapshot.date)).toDateString() }})
                </h1>
                <h2 class="mb-4">n = {{ props.solo_snapshot.n }}</h2>
                <div class="w-full max-w-full h-[60vh]">
                    <canvas ref="soloChartCanvas" class="w-full h-full"></canvas>
                </div>
            </div>
            <div class="ml-2">
                <h1 class="text-2xl font-bold">
                    Team Elo Distribution ({{ (new Date(props.team_snapshot.date)).toDateString() }})
                </h1>
                <h2 class="mb-4">n = {{ props.team_snapshot.n }}</h2>
                <div class="w-full max-w-full h-[60vh]">
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
    const renderChart = (canvasRef, snapshot) => {
        const rawBuckets = snapshot.buckets
        const entries = Object.entries(rawBuckets)

        const lastNonZeroIndex = entries.map(([, value]) => value)
            .reduce((lastIndex, value, index) => value > 0 ? index : lastIndex, 0)

        const trimmedEntries = entries.slice(0, lastNonZeroIndex + 1)
        const labels = trimmedEntries.map(([key]) => key)
        const data = trimmedEntries.map(([, value]) => value)

        new Chart(canvasRef.value, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data,
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        enabled: true,
                    },
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
            plugins: [{
                id: 'bar-value-labels',
                afterDatasetsDraw(chart) {
                    const {ctx, chartArea: {top}, scales: {x, y}} = chart

                    ctx.save()
                    chart.data.datasets[0].data.forEach((value, i) => {
                        ctx.fillStyle = '#000'
                        ctx.font = '12px sans-serif'
                        ctx.textAlign = 'center'
                        ctx.fillText(
                            value,
                            x.getPixelForValue(i),
                            y.getPixelForValue(value) - 6
                        )
                    })
                    ctx.restore()
                },
            }],
        })
    }

    renderChart(soloChartCanvas, props.solo_snapshot)
    renderChart(teamChartCanvas, props.team_snapshot)
})

</script>
