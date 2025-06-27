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
            <div>
                <Toggle
                    :options="graphTypes"
                    color="orange"
                    class="ml-auto"
                    v-model="selectedGraphType"
                />
            </div>

            <PlayerTeamSearch @row-clicked="onPlayerTeamClick" />

            <DateSelector
                v-model="selectedDate"
                :availableDates="availableDates"
                @update:model-value="updateCharts"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-8">
            <div class="bg-white p-4 md:p-6 rounded-xl shadow-md">
                <div class="flex justify-between items-start mb-3 md:mb-4">
                    <h2 class="text-lg md:text-2xl font-bold text-gray-800">
                        Solo Rating Distribution
                    </h2>
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
                :show-modal="showModal"
                :leaderboard-row="selectedLeaderboardRow"
                :rating-history="playerRatingHistory"
                :loading="isLoadingHistory"
                @close="closeModal"
            />
        </transition>
    </div>
    <Footer />
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Chart, registerables } from "chart.js";
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
import { useRatingChart } from "@/Composables/useRatingChart";
import Toggle from "@/Components/Toggle.vue";
import { useApiClient } from "@/Composables/useApiClient.ts";

Chart.defaults.animation = false;
Chart.register(...registerables);

interface Props {
    solo_snapshots: Snapshot[];
    team_snapshots: Snapshot[];
    solo_percentile_snapshots: Snapshot[];
    team_percentile_snapshots: Snapshot[];
    range_dates: string[];
    percentile_dates: string[];
    leaderboard: Player[];
}

const props = defineProps<Props>();

const { getRateableHistory, getSnapshotForDate } = useApiClient();

const graphTypes = [
    { label: "Range", value: "elo_range" },
    { label: "Percentile", value: "percentile" },
];
const selectedGraphType = ref<string>("elo_range");
watch([selectedGraphType], () => {
    initializeCharts();
});

const selectedLeaderboardRow = ref<LeaderboardRow>(EMPTY_LEADERBOARD_ROW);
const showModal = ref<boolean>(false);
const closeModal = () => (showModal.value = false);
const playerRatingHistory = ref<RatingChange[]>([]);
const isLoadingHistory = ref<boolean>(false);

const ratingHistoryCache = ref<Record<string, RatingChange[]>>({});
const onPlayerTeamClick = async (event: { rateable: LeaderboardRow }) => {
    const rateable = event.rateable;
    selectedLeaderboardRow.value = rateable;

    setTimeout(() => (showModal.value = true), 25);
    if (ratingHistoryCache.value[rateable.id]?.length > 0) {
        playerRatingHistory.value = ratingHistoryCache.value[rateable.id];

        return;
    }

    await getAndSetRateableHistory(rateable);
};
const getAndSetRateableHistory = async (rateable: LeaderboardRow) => {
    isLoadingHistory.value = true;
    const history = await getRateableHistory(rateable.type, rateable.id);

    const ratingChanges = history.data ?? [];

    if (ratingChanges.length === 0) {
        isLoadingHistory.value = false;

        return;
    }

    ratingHistoryCache.value[rateable.id] = ratingChanges;

    playerRatingHistory.value = ratingChanges.sort(
        (a: RatingChange, b: RatingChange) =>
            new Date(a.created_at).getTime() - new Date(b.created_at).getTime(),
    );
    isLoadingHistory.value = false;
};

const resizeTimer = ref<ReturnType<typeof setTimeout> | null>(null);
const handleResize = () => {
    if (resizeTimer.value) {
        clearTimeout(resizeTimer.value);
    }

    resizeTimer.value = setTimeout(() => {
        updateCharts();
    }, 250);
};
onMounted(() => {
    window.addEventListener("resize", handleResize);
});
onBeforeUnmount(() => {
    window.removeEventListener("resize", handleResize);
    if (resizeTimer.value) {
        clearTimeout(resizeTimer.value);
    }
});

const soloChartCanvas = ref<HTMLCanvasElement>();
const teamChartCanvas = ref<HTMLCanvasElement>();

const soloChartInstance = ref<Chart | null>(null);
const teamChartInstance = ref<Chart | null>(null);

const soloSnapshots = ref(props.solo_snapshots);
const teamSnapshots = ref(props.team_snapshots);

const availableDates = computed<Date[]>(() => {
    if (selectedGraphType.value === "elo_range") {
        return [
            ...new Set([...props.range_dates.map((s) => new Date(s))]),
        ].sort((a, b) => b.getTime() - a.getTime());
    }

    return [
        ...new Set([...props.percentile_dates.map((s) => new Date(s))]),
    ].sort((a, b) => b.getTime() - a.getTime());
});

const selectedDate = ref<Date>(availableDates.value[0]);
const dateObjectToYmdString = (date: Date) => date.toISOString().split("T")[0];
const currentSoloRangeSnapshot = computed<Snapshot | undefined>(() =>
    soloSnapshots.value.find(
        (s) => s.date === dateObjectToYmdString(selectedDate.value),
    ),
);
const currentTeamRangeSnapshot = computed<Snapshot | undefined>(() =>
    teamSnapshots.value.find(
        (s) => s.date === dateObjectToYmdString(selectedDate.value),
    ),
);
const currentSoloPercentileSnapshot = computed<Snapshot | undefined>(() =>
    props.solo_percentile_snapshots.find(
        (s) => s.date === dateObjectToYmdString(selectedDate.value),
    ),
);
const currentTeamPercentileSnapshot = computed<Snapshot | undefined>(() =>
    props.team_percentile_snapshots.find(
        (s) => s.date === dateObjectToYmdString(selectedDate.value),
    ),
);
watch(selectedDate, async () => {
    if (
        soloSnapshots.value.find(
            (s) => s.date === dateObjectToYmdString(selectedDate.value),
        ) &&
        teamSnapshots.value.find(
            (s) => s.date === dateObjectToYmdString(selectedDate.value),
        )
    ) {
        return;
    }

    const snapshots = await getSnapshotForDate(
        dateObjectToYmdString(selectedDate.value),
    );

    if (snapshots.error !== undefined) {
        return;
    }

    soloSnapshots.value.push(snapshots?.data?.solo as Snapshot);
    teamSnapshots.value.push(snapshots?.data?.team as Snapshot);
});

const { renderRangeChart, renderPercentileChart } = useRatingChart();

const updateCharts = () => {
    if (selectedGraphType.value === "elo_range") {
        if (
            !props.range_dates.includes(
                dateObjectToYmdString(selectedDate.value),
            )
        ) {
            selectedDate.value = new Date(
                props.range_dates[props.range_dates.length - 1],
            );
        }
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
    }

    if (selectedGraphType.value === "percentile") {
        if (
            !props.percentile_dates.includes(
                dateObjectToYmdString(selectedDate.value),
            )
        ) {
            selectedDate.value = new Date(
                props.percentile_dates[props.percentile_dates.length - 1],
            );
        }

        renderPercentileChart(
            soloChartCanvas,
            currentSoloPercentileSnapshot.value,
            false,
            soloChartInstance,
        );

        renderPercentileChart(
            teamChartCanvas,
            currentTeamPercentileSnapshot.value,
            true,
            teamChartInstance,
        );
    }
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
