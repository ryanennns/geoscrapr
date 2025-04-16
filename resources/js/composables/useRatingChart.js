import {Chart} from 'chart.js'

export function useRatingChart() {
    const renderChart = (canvasRef, snapshot, isTeamChart = false, instanceRef) => {
        if (!snapshot || !canvasRef?.value) return

        const entries = Object.entries(snapshot.buckets)
        const nonZeroEntries = entries.filter(([, v]) => v > 0)

        const labels = nonZeroEntries.map(([key]) => key)
        const data = nonZeroEntries.map(([, val]) => val)

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
                            label: ctx => `Players: ${ctx.raw.toLocaleString()}`,
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

    return {renderChart}
}
