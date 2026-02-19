import { Chart } from "chart.js";

export function useRatingChart() {
    const renderRangeChart = (
        canvasRef,
        snapshot,
        isTeamChart = false,
        instanceRef,
        dark = false,
    ) => {
        if (!snapshot || !canvasRef?.value) {
            return;
        }

        const entries = Object.entries(snapshot.buckets);

        let numberOfElementsToTrimOffTheEnd = 0;
        const reversedEntries = [...entries].reverse();
        for (let i = 0; i < entries.length; i++) {
            if (reversedEntries[i][1] > 0) {
                numberOfElementsToTrimOffTheEnd = i;
                break;
            }
        }

        let numberOfElementsToTrimOffTheStart = 0;
        for (let i = 0; i < entries.length; i++) {
            if (entries[i][1] > 0) {
                numberOfElementsToTrimOffTheStart = i;
                break;
            }
        }

        const trimmedEntries = entries.slice(
            numberOfElementsToTrimOffTheStart,
            entries.length - numberOfElementsToTrimOffTheEnd,
        );

        const labels = trimmedEntries.map(([key]) => key);
        const data = trimmedEntries.map(([, val]) => val);

        const numericLabels = labels.map((label) =>
            parseInt(label.match(/\d+/)?.[0] || "0", 10),
        );
        const min = Math.min(...numericLabels);
        const max = Math.max(...numericLabels);

        const backgroundColors = numericLabels.map((r) => {
            const norm = (r - min) / (max - min || 1);
            return isTeamChart
                ? `rgba(${110 - norm * 70}, ${231 - norm * 50}, ${183 + norm * 30}, 0.75)`
                : `rgba(${147 + norm * 100}, ${197 - norm * 160}, ${253 - norm * 25}, 0.75)`;
        });
        const borderColors = backgroundColors.map((c) =>
            c.replace("0.75", "1.0"),
        );

        if (instanceRef.value) {
            instanceRef.value.destroy();
        }

        const tickColor = dark ? "#94a3b8" : "#64748b";
        const gridColor = dark ? "rgba(255,255,255,0.08)" : "rgba(0,0,0,0.05)";
        const labelColor = dark ? "#e2e8f0" : "#292929";

        instanceRef.value = new Chart(canvasRef.value, {
            type: "bar",
            data: {
                labels,
                datasets: [
                    {
                        data,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        enabled: true,
                        backgroundColor: "rgba(0,0,0,0.8)",
                        padding: 12,
                        titleFont: { size: 14, weight: "bold" },
                        bodyFont: { size: 13 },
                        callbacks: {
                            title: (items) => `Rating Range: ${items[0].label}`,
                            label: (ctx) =>
                                `Players: ${ctx.raw.toLocaleString()}`,
                        },
                    },
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { font: { size: 12 }, color: tickColor },
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            maxRotation: 45,
                            minRotation: 45,
                            color: tickColor,
                        },
                    },
                },
                animation: {
                    duration: 1000,
                    easing: "easeOutQuart",
                },
            },
            plugins: [
                {
                    id: "bar-value-labels",
                    afterDatasetsDraw(chart) {
                        if (window.innerWidth <= 675) {
                            return;
                        }

                        const {
                            ctx,
                            scales: { x, y },
                        } = chart;
                        ctx.save();
                        chart.data.datasets[0].data.forEach((value, i) => {
                            ctx.fillStyle = labelColor;
                            ctx.font = "bold 12px sans-serif";
                            ctx.textAlign = "center";
                            ctx.fillText(
                                formatNumber(Number(value)),
                                x.getPixelForValue(i),
                                y.getPixelForValue(value) - 8,
                            );
                        });
                        ctx.restore();
                    },
                },
            ],
        });
    };

    const renderPercentileChart = (
        canvasRef,
        snapshot,
        isTeamChart = false,
        instanceRef,
        dark = false,
    ) => {
        if (!snapshot || !canvasRef?.value) {
            return;
        }

        const entries = Object.entries(snapshot.buckets);
        const nonZeroEntries = entries.filter(([, v]) => v > 0);

        const labels = nonZeroEntries.map(([key]) => key);
        const data = nonZeroEntries.map(([, val]) => val);

        const numericLabels = labels.map((label) => parseFloat(label));
        const min = Math.min(...numericLabels);
        const max = Math.max(...numericLabels);

        const backgroundColors = numericLabels.map((percentile) => {
            const norm = (percentile - min) / (max - min || 1);
            return isTeamChart
                ? `rgba(${110 - norm * 70}, ${231 - norm * 50}, ${183 + norm * 30}, 0.75)`
                : `rgba(${147 + norm * 100}, ${197 - norm * 160}, ${253 - norm * 25}, 0.75)`;
        });
        const borderColors = backgroundColors.map((c) =>
            c.replace("0.75", "1.0"),
        );

        if (instanceRef.value) {
            instanceRef.value.destroy();
        }

        const tickColor = dark ? "#94a3b8" : "#64748b";
        const gridColor = dark ? "rgba(255,255,255,0.08)" : "rgba(0,0,0,0.05)";

        instanceRef.value = new Chart(canvasRef.value, {
            type: "bar",
            data: {
                labels,
                datasets: [
                    {
                        data,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        enabled: true,
                        backgroundColor: "rgba(0,0,0,0.8)",
                        padding: 12,
                        titleFont: { size: 14, weight: "bold" },
                        bodyFont: { size: 13 },
                        callbacks: {
                            title: (items) => {
                                const percentile = items[0].label;
                                const suffix = percentile.includes(".")
                                    ? "th"
                                    : getOrdinalSuffix(percentile);
                                return `${percentile}${suffix} Percentile`;
                            },
                            label: (ctx) => `Elo: ${ctx.raw.toLocaleString()}`,
                        },
                    },
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { font: { size: 12 }, color: tickColor },
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            maxRotation: 45,
                            minRotation: 45,
                            color: tickColor,
                            callback: function (value, index) {
                                const label = this.getLabelForValue(value);
                                if (labels.length > 20) {
                                    return index % 5 === 0 ? label : "";
                                }
                                return label;
                            },
                        },
                    },
                },
                animation: {
                    duration: 1000,
                    easing: "easeOutQuart",
                },
            },
        });
    };

    const getOrdinalSuffix = (num) => {
        const numInt = parseInt(num);
        const lastDigit = numInt % 10;
        const lastTwoDigits = numInt % 100;

        if (lastTwoDigits >= 11 && lastTwoDigits <= 13) {
            return "th";
        }

        switch (lastDigit) {
            case 1:
                return "st";
            case 2:
                return "nd";
            case 3:
                return "rd";
            default:
                return "th";
        }
    };

    return { renderRangeChart, renderPercentileChart };
}

const formatNumber = (num) => {
    return num >= 1000
        ? (num / 1000).toFixed(1).replace(/\.0$/, "") + "k"
        : num.toLocaleString();
};
