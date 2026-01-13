<template>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-8">
        <div
            v-for="config in histogramConfigs"
            class="bg-white p-4 md:p-6 rounded-xl shadow-md"
        >
            <div class="flex justify-between items-start mb-3 md:mb-4">
                <h2 class="text-lg md:text-2xl font-bold text-gray-800">
                    {{ config.title }}
                </h2>
                <Badge
                    :text="config.badgeText"
                    class="bg-blue-100 text-blue-800 text-xs md:text-sm ml-1"
                />
            </div>
            <div class="w-full h-64 md:h-[62vh]">
                <canvas :ref="config.ref" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Badge from "@/Components/Badge.vue";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import type { Snapshot } from "@/Types/core.ts";
import { useRatingChart } from "@/Composables/useRatingChart";
import { Chart } from "chart.js";

const { renderRangeChart } = useRatingChart();

const props = defineProps<{
    soloSnapshots: Snapshot[];
    teamSnapshots: Snapshot[];
    selectedDate: Date;
}>();

const selectedDateRef = computed(() => props.selectedDate);

const currentSoloRangeSnapshot = computed<Snapshot | undefined>(() =>
    props.soloSnapshots.find(
        (s) => s.date === dateObjectToYmdString(props.selectedDate),
    ),
);
const currentTeamRangeSnapshot = computed<Snapshot | undefined>(() =>
    props.teamSnapshots.find(
        (s) => s.date === dateObjectToYmdString(props.selectedDate),
    ),
);

const histogramConfigs = computed(() => [
    {
        title: "Solo Rating Distribution",
        badgeText: `n = ${currentSoloRangeSnapshot.value?.n.toLocaleString() || 0}`,
        ref: "soloChartCanvas",
    },
    {
        title: "Team Rating Distribution",
        badgeText: `n = ${currentTeamRangeSnapshot.value?.n.toLocaleString() || 0}`,
        ref: "teamChartCanvas",
    },
]);

const resizeTimer = ref<ReturnType<typeof setTimeout> | null>(null);
const handleResize = () => {
    if (resizeTimer.value) {
        clearTimeout(resizeTimer.value);
    }

    resizeTimer.value = setTimeout(() => updateCharts(), 250);
};

const dateObjectToYmdString = (date: Date) => date.toISOString().split("T")[0];

const soloChartCanvas = ref<HTMLCanvasElement>();
const teamChartCanvas = ref<HTMLCanvasElement>();

const soloChartInstance = ref<Chart | null>(null);
const teamChartInstance = ref<Chart | null>(null);

const updateCharts = () => {
    [
        [
            soloChartCanvas,
            currentSoloRangeSnapshot.value,
            false,
            soloChartInstance,
        ],
        [
            teamChartCanvas,
            currentTeamRangeSnapshot.value,
            true,
            teamChartInstance,
        ],
    ].forEach((args) => renderRangeChart(...args));
};

const initializeCharts = () => {
    [soloChartInstance, teamChartInstance].forEach((c) => {
        if (c.value) {
            c.value.destroy();
            c.value = null;
        }
    });

    updateCharts();
};

watch(
    [currentSoloRangeSnapshot, currentTeamRangeSnapshot, selectedDateRef],
    updateCharts,
);

onMounted(() => {
    window.addEventListener("resize", handleResize);

    initializeCharts();
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", handleResize);
    if (resizeTimer.value) {
        clearTimeout(resizeTimer.value);
    }
});
</script>
