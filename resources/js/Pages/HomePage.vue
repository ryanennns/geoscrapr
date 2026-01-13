<template>
    <div class="p-4 md:p-8 bg-gray-50 min-h-screen">
        <Header />

        <div
            class="flex flex-col md:flex-row justify-center items-center gap-3 md:gap-4 mb-6 md:mb-10 max-w-3xl mx-auto"
        >
            <PlayerTeamSearch @row-clicked="onPlayerTeamClick" />

            <DateSelector
                v-model="selectedDate"
                :availableDates="availableDates"
            />
        </div>

        <EloRangeHistograms
            :solo-snapshots="solo_snapshots"
            :team-snapshots="team_snapshots"
            :selected-date="selectedDate"
        />

        <PlayerLeaderboard
            :playersOrTeams="props.leaderboard"
            @player-click="onPlayerTeamClick"
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
import { computed, onMounted, ref, watch } from "vue";
import { Chart, registerables } from "chart.js";
import "@vuepic/vue-datepicker/dist/main.css";
import PlayerTeamSearch from "../Components/PlayerTeamSearch.vue";
import Footer from "@/Components/Footer.vue";
import DateSelector from "../Components/DateSelector.vue";
import PlayerLeaderboard from "./PlayerLeaderboard.vue";
import RatingHistoryModal from "../Components/RatingHistoryModal.vue";
import {
    EMPTY_LEADERBOARD_ROW,
    type LeaderboardRow,
    type Player,
    type RatingChange,
    type Snapshot,
} from "@/Types/core.ts";
import { useApiClient } from "@/Composables/useApiClient";
import { useUrlParams } from "@/Composables/useUrlParams";
import { usePlayerUtils } from "@/Composables/usePlayerUtils";
import Header from "@/Components/HomePage/Header.vue";
import EloRangeHistograms from "@/Components/HomePage/EloRangeHistograms.vue";

Chart.defaults.animation = false;
Chart.register(...registerables);

interface Props {
    solo_snapshots: Snapshot[];
    team_snapshots: Snapshot[];
    range_dates: string[];
    leaderboard: Player[];
}

const props = defineProps<Props>();

const { getRateableHistory, getSnapshotForDate, getRateable } = useApiClient();
const { getId, setId, clearId } = useUrlParams();
const { rateableToLeaderboardRows } = usePlayerUtils();

const selectedLeaderboardRow = ref<LeaderboardRow>(EMPTY_LEADERBOARD_ROW);
const showModal = ref<boolean>(false);
const closeModal = () => {
    showModal.value = false;
    clearId();
};
const playerRatingHistory = ref<RatingChange[]>([]);
const isLoadingHistory = ref<boolean>(false);

const ratingHistoryCache = ref<Record<string, RatingChange[]>>({});
const onPlayerTeamClick = async (event: { rateable: LeaderboardRow }) => {
    setId(event.rateable.id);

    const rateable = event.rateable;
    selectedLeaderboardRow.value = rateable;

    setTimeout(() => (showModal.value = true), 25);
    if (ratingHistoryCache.value[rateable.id]?.length > 0) {
        playerRatingHistory.value = ratingHistoryCache.value[rateable.id];

        return;
    }

    await getAndSetRateableHistory(rateable);
};

const getAndSetRateableHistory = async (leaderboardRow: LeaderboardRow) => {
    isLoadingHistory.value = true;
    const history = await getRateableHistory(
        leaderboardRow.type,
        leaderboardRow.id,
    );

    const ratingChanges = history.data ?? [];

    ratingHistoryCache.value[leaderboardRow.id] = ratingChanges;

    playerRatingHistory.value = ratingChanges.sort(
        (a: RatingChange, b: RatingChange) =>
            new Date(a.created_at).getTime() - new Date(b.created_at).getTime(),
    );
    isLoadingHistory.value = false;
};

onMounted(async () => {
    const id = getId();
    if (id) {
        const rateable = (await getRateable(id)).data;

        if (!rateable) {
            clearId();
            return;
        }

        await getAndSetRateableHistory(rateableToLeaderboardRows(rateable));
        selectedLeaderboardRow.value = rateableToLeaderboardRows(rateable);

        showModal.value = true;
    }
});
const soloSnapshots = ref(props.solo_snapshots);
const teamSnapshots = ref(props.team_snapshots);

const availableDates = computed<Date[]>(() => {
    return [...new Set([...props.range_dates.map((s) => new Date(s))])].sort(
        (a, b) => b.getTime() - a.getTime(),
    );
});

const selectedDate = ref<Date>(availableDates.value[0] ?? new Date());
const dateObjectToYmdString = (date: Date) => date.toISOString().split("T")[0];
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
