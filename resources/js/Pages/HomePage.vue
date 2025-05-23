<template>
    <div class="p-4 md:p-8 bg-gray-50 min-h-screen">
        <div class="mb-6 md:mb-8 text-center">
            <h1 class="text-xl md:text-3xl font-bold text-indigo-800">
                GeoGuessr Ranking Distributions
            </h1>
            <p class="text-sm md:text-base text-gray-600 lg:visible">
                Explore player statistics, historical rating distributions, and
                more
            </p>
        </div>

        <div
            class="flex flex-col md:flex-row justify-center items-center gap-3 md:gap-4 mb-6 md:mb-10 max-w-3xl mx-auto"
        >
            <PlayerTeamSearch @row-clicked="onPlayerTeamClick"/>

            <DateSelector
                v-model="selectedDate"
                :availableDates="availableDatesObjects"
                @update:model-value="updateCharts"
                class="w-full md:w-auto"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-8">
            <div class="bg-white p-4 md:p-6 rounded-xl shadow-md">
                <div class="flex justify-between items-start mb-3 md:mb-4">
                    <h2 class="text-lg md:text-2xl font-bold text-gray-800">
                        Solo Rating Distribution
                    </h2>
                    <Toggle
                        :options="graphTypes"
                        color="blue"
                        class="ml-auto opacity-40"
                    />
                    <Badge
                        :text="`n = ${currentSoloRangeSnapshot?.n.toLocaleString() || 0}`"
                        class="bg-blue-100 text-blue-800 text-xs md:text-sm ml-1"
                    />
                </div>
                <div class="w-full h-64 md:h-[62vh]">
                    <canvas
                        ref="soloChartCanvas"
                        class="w-full h-full"
                    ></canvas>
                </div>
            </div>

            <div class="bg-white p-4 md:p-6 rounded-xl shadow-md">
                <div class="flex justify-between items-start mb-3 md:mb-4">
                    <h2 class="text-lg md:text-2xl font-bold text-gray-800">
                        Team Rating Distribution
                    </h2>
                    <Toggle
                        :options="graphTypes"
                        color="green"
                        class="ml-auto opacity-40"
                    />
                    <Badge
                        :text="`n = ${currentTeamRangeSnapshot?.n.toLocaleString() || 0}`"
                        class="bg-blue-100 text-blue-800 text-xs md:text-sm ml-1"
                    />
                </div>
                <div class="w-full h-64 md:h-[62vh]">
                    <canvas
                        ref="teamChartCanvas"
                        class="w-full h-full"
                    ></canvas>
                </div>
            </div>
        </div>

        <PlayerLeaderboard
            :playersOrTeams="props.leaderboard"
            @player-click="onPlayerTeamClick"
            class="mt-6"
        />
        <transition name="fade">
            <RatingHistoryModal
                v-show="selectedLeaderboardRow !== null"
                :show-modal="showModal"
                :leaderboard-row="selectedLeaderboardRow"
                :rating-history="playerRatingHistory"
                :loading="isLoadingHistory"
                @close="closeModal"
            />
        </transition>
    </div>
    <Footer/>
</template>

<script setup lang="ts">
import {computed, onBeforeUnmount, onMounted, ref, watch} from "vue";
import {Chart, registerables} from "chart.js";
import "@vuepic/vue-datepicker/dist/main.css";
import PlayerTeamSearch from "../Components/PlayerTeamSearch.vue";
import Footer from "@/Components/Footer.vue";
import DateSelector from "../Components/DateSelector.vue";
import PlayerLeaderboard from "./PlayerLeaderboard.vue";
import RatingHistoryModal from "../Components/RatingHistoryModal.vue";
import Badge from "../Components/Badge.vue";
import {
    EMPTY_LEADERBOARD_ROW,
    type LeaderboardRow,
    type Player,
    type RatingChange,
    type Snapshot,
} from "@/Types/core.ts";
import {useRatingChart} from "@/composables/useRatingChart";
import Toggle from "@/Components/Toggle.vue";

interface Props {
    solo_snapshots: Snapshot[];
    team_snapshots: Snapshot[];
    solo_percentile_snapshots: Snapshot[];
    team_percentile_snapshots: Snapshot[];
    dates: string[];
    leaderboard: Player[];
}

const graphTypes = [
    {label: "Elo Range", value: "elo_range"},
    {label: "Percentile", value: "percentile"},
];
const selectedSoloGraphType = ref<string>("elo_range");
const selectedTeamGraphType = ref<string>("elo_range");

watch([selectedSoloGraphType, selectedTeamGraphType], () => {
    updateCharts();
});

const props = defineProps<Props>();

const resizeTimer = ref<ReturnType<typeof setTimeout> | null>(null);

// modal
const selectedLeaderboardRow = ref<LeaderboardRow>(EMPTY_LEADERBOARD_ROW);
const showModal = ref<boolean>(false);
const closeModal = () => (showModal.value = false);
const playerRatingHistory = ref<RatingChange[]>([]);
const isLoadingHistory = ref<boolean>(false);

const ratingHistoryCache = ref<Record<string, RatingChange[]>>({});
const onPlayerTeamClick = async (event: { rateable: LeaderboardRow }) => {
    console.log(event);
    const rateable = event.rateable;
    selectedLeaderboardRow.value = rateable;

    setTimeout(() => (showModal.value = true), 25);
    if (ratingHistoryCache.value[rateable.id]?.length > 0) {
        playerRatingHistory.value = ratingHistoryCache.value[rateable.id];

        return;
    }

    try {
        isLoadingHistory.value = true;
        const response = await fetch(
            `/${rateable.type}s/history/${rateable.id}`,
        );
        if (!response.ok) {
            throw new Error("Failed to fetch player details");
        }

        const historyData = await response.json();

        ratingHistoryCache.value[rateable.id] = historyData.data;

        playerRatingHistory.value = historyData.data.sort(
            (a: RatingChange, b: RatingChange) =>
                new Date(a.created_at).getTime() -
                new Date(b.created_at).getTime(),
        );
    } catch (err) {
        console.error("Error loading player details:", err);
    } finally {
        setTimeout(
            () => (isLoadingHistory.value = false),
            150 + Math.random() * 150,
        );
    }
};

const handleResize = () => {
    if (resizeTimer.value) {
        clearTimeout(resizeTimer.value);
    }

    resizeTimer.value = setTimeout(() => {
        updateCharts();
    }, 250);
};

onMounted(() => {
    console.log(props);

    window.addEventListener("resize", handleResize);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", handleResize);
    if (resizeTimer.value) {
        clearTimeout(resizeTimer.value);
    }
});

Chart.defaults.animation = false;
Chart.register(...registerables);

const soloChartCanvas = ref<HTMLCanvasElement>();
const teamChartCanvas = ref<HTMLCanvasElement>();

const soloChartInstance = ref<Chart | null>(null);
const teamChartInstance = ref<Chart | null>(null);

const soloSnapshots = ref(props.solo_snapshots);
const teamSnapshots = ref(props.team_snapshots);

const availableDates = computed(() => {
    const soloDates = props.solo_snapshots.map((s) => s.date);
    const teamDates = props.team_snapshots.map((s) => s.date);

    return [...new Set([...soloDates, ...teamDates])].sort().reverse();
});

function parseLocalDate(dateStr: string) {
    const [year, month, day] = dateStr.split("-").map(Number);
    return new Date(year, month - 1, day);
}

const availableDatesObjects = computed<Date[]>(() => {
    return props.dates.map(parseLocalDate);
});

const selectedDate = ref<Date>(
    availableDates.value[0]
        ? parseLocalDate(availableDates.value[0])
        : new Date(),
);

const formatDate = (date: Date) => date.toISOString().split("T")[0];

const fetchSnapshot = async (date: string) => {
    const response = await fetch(`/snapshots?date=${date}`);

    if (!response.ok) {
        throw new Error(`Failed to fetch snapshot for ${date}.`);
    }

    return (await response.json())?.data;
};

const currentSoloRangeSnapshot = computed<Snapshot | undefined>(() =>
    soloSnapshots.value.find((s) => s.date === formatDate(selectedDate.value)),
);
const currentTeamRangeSnapshot = computed<Snapshot | undefined>(() =>
    teamSnapshots.value.find((s) => s.date === formatDate(selectedDate.value)),
);
const currentSoloPercentileSnapshot = computed<Snapshot | undefined>(() =>
    props.solo_percentile_snapshots.find(
        (s) => s.date === formatDate(selectedDate.value),
    ),
);
const currentTeamPercentileSnapshot = computed<Snapshot | undefined>(() =>
    props.team_percentile_snapshots.find(
        (s) => s.date === formatDate(selectedDate.value),
    ),
);

watch(selectedDate, async () => {
    if (
        soloSnapshots.value.find(
            (s) => s.date === formatDate(selectedDate.value),
        ) &&
        teamSnapshots.value.find(
            (s) => s.date === formatDate(selectedDate.value),
        )
    ) {
        return;
    }

    const snapshots = await fetchSnapshot(formatDate(selectedDate.value));

    soloSnapshots.value.push(snapshots.solo);
    teamSnapshots.value.push(snapshots.team);
});

const {renderRangeChart, renderPercentileChart} = useRatingChart();

const updateCharts = () => {
    renderRangeChart(
        soloChartCanvas,
        currentSoloRangeSnapshot.value,
        false,
        soloChartInstance,
    );
    renderRangeChart(
        teamChartCanvas,
        currentTeamRangeSnapshot.value,
        true,
        teamChartInstance,
    );
};

const initializeCharts = () => {
    if (soloChartInstance.value) {
        soloChartInstance.value.destroy();
        soloChartInstance.value = null;
    }

    if (teamChartInstance.value) {
        teamChartInstance.value.destroy();
        teamChartInstance.value = null;
    }

    updateCharts();
};

onMounted(() => {
    console.log('snickers', props.solo_percentile_snapshots)
    initializeCharts();
});

watch(
    [currentSoloRangeSnapshot, currentTeamRangeSnapshot, selectedDate],
    updateCharts,
);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
