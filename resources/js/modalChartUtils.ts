import type { LeaderboardRow, RatingChange } from "@/Types/core.ts";
import { Chart, type TooltipItem } from "chart.js";

const getDatesInRange = (startDate: Date, endDate: Date) => {
    const dates: Date[] = [];
    const currentDate = new Date(startDate);
    const endDateCopy = new Date(endDate);

    currentDate.setHours(0, 0, 0, 0);
    endDateCopy.setHours(0, 0, 0, 0);

    endDateCopy.setDate(endDateCopy.getDate() + 1);

    while (currentDate < endDateCopy) {
        dates.push(new Date(currentDate));
        currentDate.setDate(currentDate.getDate() + 1);
    }

    return dates;
};

const formatDateString = (date: Date) => {
    return date.toISOString().split("T")[0];
};

interface GetRatingHistoryChartDataProps {
    daysToShow: number;
    ratingHistory: RatingChange[];
    leaderboardRow: LeaderboardRow;
}

export const getRatingHistoryChartData = ({
    daysToShow,
    ratingHistory,
    leaderboardRow,
}: GetRatingHistoryChartDataProps) => {
    const today = new Date();
    const startDate = new Date(today);
    startDate.setDate(today.getDate() - daysToShow);

    const allDates = getDatesInRange(startDate, today);

    const ratingsByDate = Object.fromEntries(
        ratingHistory.map((record) => [
            formatDateString(new Date(record.created_at)),
            record.rating,
        ]),
    );

    const sortedRatingHistory = [...ratingHistory].sort(
        (a, b) =>
            new Date(b.created_at).getTime() - new Date(a.created_at).getTime(),
    );

    const leftOfStartDate = sortedRatingHistory.find(
        (r) => new Date(r.created_at) < startDate,
    );
    const oldestRating = sortedRatingHistory[sortedRatingHistory.length - 1];

    const labels: string[] = [];
    const data: number[] = [];

    let mostRecentRating =
        leftOfStartDate?.rating ??
        oldestRating.rating ??
        leaderboardRow?.rating;

    allDates.forEach((date) => {
        const dateString = formatDateString(date);
        labels.push(date.toLocaleDateString());

        if (ratingsByDate[dateString]) {
            mostRecentRating = ratingsByDate[dateString];
        }

        data.push(mostRecentRating);
    });

    return { labels, data };
};

interface CreateRatingChartProps {
    ctx: CanvasRenderingContext2D;
    labels: string[];
    p1: number[];
    p2?: number[];
    gradient: CanvasGradient;
    yMin: number;
    yMax: number;
    step: number;
    daysToShow: number;
}

export const createRatingChart = ({
    ctx,
    labels,
    p1,
    p2,
    gradient,
    yMin,
    yMax,
    step,
    daysToShow,
}: CreateRatingChartProps) => {
    const colors = [
        { base: "rgba(220, 38, 38,", name: "red" }, // red
        { base: "rgba(37, 99, 235,", name: "blue" }, // blue
    ];

    const datasets = [p1, p2]
        .filter((p) => !!p)
        .map((data, index) => {
            const color = colors[index % colors.length].base;
            return {
                label: `Rating ${index + 1}`,
                data,
                backgroundColor: gradient,
                borderColor: `${color} 0.9)`,
                borderWidth: 2.5,
                tension: 0,
                fill: true,
                pointBackgroundColor: "#ffffff",
                pointBorderColor: `${color} 1)`,
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: "white",
                pointHoverBorderColor: `${color} 1)`,
                pointHoverBorderWidth: 3,
                spanGaps: true,
            };
        });

    return new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: "index",
                intersect: false,
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: yMin,
                    max: yMax,
                    ticks: {
                        stepSize: step,
                        font: {
                            size: 11,
                        },
                        padding: 8,
                        color: "#64748b",
                    },
                    grid: {
                        color: "rgba(226, 232, 240, 0.8)",
                    },
                    border: {
                        dash: [4, 4],
                    },
                },
                x: {
                    title: {
                        display: true,
                        text: `Rating History (Last ${daysToShow / 7} Weeks)`,
                        font: {
                            size: 12,
                        },
                        padding: {
                            top: 10,
                        },
                        color: "#1e293b",
                    },
                    ticks: {
                        maxTicksLimit: Math.min(10, labels.length),
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10,
                        },
                        padding: 5,
                        color: "#64748b",
                    },
                    grid: {
                        color: "rgba(226, 232, 240, 0.6)",
                    },
                },
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: "rgba(255, 255, 255, 0.95)",
                    titleColor: "#1e293b",
                    bodyColor: "#334155",
                    borderColor: "#e2e8f0",
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 6,
                    titleFont: {
                        size: 13,
                        weight: "bold",
                    },
                    bodyFont: {
                        size: 12,
                    },
                    callbacks: {
                        title(tooltipItems: TooltipItem<"line">[]) {
                            return tooltipItems[0].label;
                        },
                        label(context: TooltipItem<"line">) {
                            return `Rating: ${(context.raw as number).toLocaleString()}`;
                        },
                    },
                },
            },
            animation: {
                duration: 1500,
                easing: "easeOutQuart",
            },
        },
    });
};
