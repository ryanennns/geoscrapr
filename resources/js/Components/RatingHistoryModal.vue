<template>
    <transition name="fade">
        <div
            v-if="props.showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/75"
            @click.self="onClose"
        >
            <div
                class="flex flex-col bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg shadow-lg px-6 pt-6 pb-5 w-full max-w-2xl transition-all duration-300"
                :class="wrapperClasses"
            >
                <div class="flex justify-between items-start">
                    <span class="grow">
                        <PlayerData
                            :leaderboard-row="leaderboardRow"
                            :loading-match-history="loadingMatchHistory"
                            :expanded="expanded"
                            :match-history="matchHistory"
                            colour="#2563eb"
                            :comparing="playerToCompareWith !== null"
                        />
                    </span>
                    <span class="grow" v-if="playerToCompareWith">
                        <PlayerData
                            :leaderboard-row="playerToCompareWith"
                            :loading-match-history="false"
                            :expanded="expanded"
                            :matchHistory="[]"
                            colour="#dc2626"
                            :comparing="true"
                        />
                    </span>
                    <div class="flex h-full items-center mr-8">
                        <div class="items-center">
                            <PlayerTeamSearch
                                v-if="showSearch"
                                @rowClicked="handleSelectPlayerToCompareWith"
                                placeholder="Compare with player..."
                                :show-teams="false"
                            />
                        </div>
                    </div>
                    <ExpandContractButton
                        v-if="
                            !isMobile &&
                            !(props.ratingHistory.length < 1 && !props.loading)
                        "
                        :expanded="expanded"
                        @toggle="toggleExpand"
                    />
                    <CloseButton v-else @close="() => onClose()" />
                </div>

                <div>
                    <LoadingSpinner
                        v-show="props.loading"
                        class="mt-12"
                        text="Loading rating history"
                    />
                    <div
                        v-show="
                            props.ratingHistory.length > 0 && !props.loading
                        "
                        class="h-full"
                    >
                        <div
                            class="w-full transition-all duration-300"
                            :class="canvasWrapperClasses"
                        >
                            <canvas ref="ratingChartCanvas" />
                        </div>
                    </div>
                    <ErrorMessage
                        class="mt-6"
                        heading="We don't have any data for this player!"
                        sub-heading="Check back later or try another player."
                        v-show="
                            props.ratingHistory.length < 1 && !props.loading
                        "
                    />
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { Chart } from "chart.js";
import ErrorMessage from "@/Components/ErrorMessage.vue";
import type { LeaderboardRow, RatingChange } from "@/Types/core.ts";
import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import ExpandContractButton from "@/Components/ExpandContractButton.vue";
import { useBrowserUtils } from "@/Composables/useBrowserUtils.ts";
import CloseButton from "@/Components/CloseButton.vue";
import { useUrlParams } from "@/Composables/useUrlParams.ts";
import { type MatchHistory, useApiClient } from "@/Composables/useApiClient.ts";
import {
    createRatingChart,
    getRatingHistoryChartData,
} from "@/modalChartUtils.ts";
import PlayerTeamSearch from "@/Components/PlayerTeamSearch.vue";
import PlayerData from "@/Components/PlayerData.vue";
import { usePlayerUtils } from "@/Composables/usePlayerUtils.ts";
import { useDarkMode } from "@/Composables/useDarkMode";

interface Props {
    showModal: boolean;
    leaderboardRow: LeaderboardRow;
    ratingHistory: RatingChange[];
    loading: boolean;
}
const props = defineProps<Props>();

const showSearch = computed<boolean>(() => {
    return expanded.value === true && props.leaderboardRow.type !== "team";
});

const { getMatchHistory, getRateableHistory, getRateable } = useApiClient();
const { rateableToLeaderboardRows } = usePlayerUtils();
const { isMobile } = useBrowserUtils();
const { get, set, clear } = useUrlParams();
const { isDark } = useDarkMode();

const emit = defineEmits(["close"]);

const daysToShow = ref<number>(14);

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key !== "Escape") {
        return;
    }

    onClose();
};

const calculateStepSize = (range: number) => {
    if (range <= 100) return 50;
    if (range <= 200) return 100;
    return 250;
};

const expanded = ref<boolean>(false);
const toggleExpand = (rerender: boolean = true) => {
    expanded.value = !expanded.value;
    expanded.value ? (daysToShow.value = 112) : (daysToShow.value = 14);
    expanded.value
        ? set("expanded", String(expanded.value))
        : clear("expanded") > clear("compare_with");

    playerToCompareWith.value = null;
    playerToCompareWithRatingHistory.value = [];

    if (rerender) {
        renderRatingChart();
    }
};

const wrapperClasses = computed<string>(() =>
    isMobile.value
        ? "h-360px"
        : expanded.value
          ? "max-w-[90vw] h-[77vh]"
          : "max-w-2xl h-[37vh]",
);
const canvasWrapperClasses = computed<string>(() =>
    isMobile.value ? "w-full h-60" : expanded.value ? "h-[65vh]" : "h-60",
);

const ratingChartInstance = ref<Chart | null>(null);
const onClose = () => {
    emit("close");

    clear("expanded");
    clear("compare_with");

    if (ratingChartInstance.value) {
        setTimeout(() => {
            ratingChartInstance.value?.destroy();
            ratingChartInstance.value = null;
        }, 300);
    }

    expanded.value = false;
    daysToShow.value = 14;

    playerToCompareWith.value = null;
    playerToCompareWithRatingHistory.value = [];
};

const ratingChartCanvas = ref<HTMLCanvasElement>();
const renderRatingChart = () => {
    if (!ratingChartCanvas.value || props.ratingHistory.length === 0) {
        return;
    }

    const ctx = ratingChartCanvas.value.getContext("2d");

    if (!ctx) {
        console.error("unable to get chart canvas context");

        return;
    }

    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
    }

    const { labels, data: p1 } = getRatingHistoryChartData({
        daysToShow: daysToShow.value,
        ratingHistory: props.ratingHistory,
        leaderboardRow: props.leaderboardRow,
    });

    if (p1.length === 0) {
        return;
    }

    let p2: number[] = [];
    if (
        playerToCompareWith.value &&
        playerToCompareWithRatingHistory.value.length > 0
    ) {
        const { data } = getRatingHistoryChartData({
            daysToShow: daysToShow.value,
            ratingHistory: playerToCompareWithRatingHistory.value,
            leaderboardRow: playerToCompareWith.value,
        });
        p2 = data;
    }

    const validData = [
        ...p1.filter((v) => v !== null),
        ...p2.filter((v) => v !== null),
    ];
    const minRating = Math.min(...validData);
    const maxRating = Math.max(...validData);

    const range = maxRating - minRating;
    const step = calculateStepSize(range);

    const buffer = step * 2;
    const yMin = Math.max(
        0,
        Math.floor((minRating - buffer / 2) / step) * step,
    );
    const yMax = Math.ceil((maxRating + buffer / 2) / step) * step;

    const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
    gradient.addColorStop(0, "rgba(79, 70, 229, 0.4)");
    gradient.addColorStop(1, "rgba(79, 70, 229, 0.05)");

    ratingChartInstance.value = createRatingChart({
        ctx,
        labels,
        p1,
        p2,
        gradient,
        yMin,
        yMax,
        step,
        daysToShow: daysToShow.value,
        dark: isDark.value,
    });
};

const playerToCompareWith = ref<LeaderboardRow | null>(null);
const playerToCompareWithRatingHistory = ref<RatingChange[]>([]);
const handleSelectPlayerToCompareWith = async (event: {
    rateable: LeaderboardRow;
}) => {
    playerToCompareWith.value = event.rateable;

    const result = await getRateableHistory(
        "player",
        playerToCompareWith.value.id,
    );
    playerToCompareWithRatingHistory.value = result.data || [];

    set("compare_with", String(playerToCompareWith.value.id));

    renderRatingChart();
};

watch(isDark, () => {
    if (props.showModal) {
        renderRatingChart();
    }
});

watch(
    () => [props.ratingHistory, props.showModal, props.leaderboardRow],
    async ([_, show]) => {
        if (show && props.ratingHistory) {
            await nextTick();
            renderRatingChart();
        }
    },
    { immediate: true },
);

const loadingMatchHistory = ref<boolean>(false);
const matchHistory = ref<MatchHistory[]>([]);
watch(
    () => props.showModal,
    async (show) => {
        if (show) {
            loadingMatchHistory.value = true;
            const stuff = await getMatchHistory(props.leaderboardRow.id);
            matchHistory.value = stuff.data?.slice(0, 6) || [];
            loadingMatchHistory.value = false;
        }

        show
            ? window.addEventListener("keydown", handleKeydown)
            : window.removeEventListener("keydown", handleKeydown);
        show
            ? (document.documentElement.style.overflow = "hidden")
            : (document.documentElement.style.overflow = "auto");
    },
);

onMounted(async () => {
    if (isMobile.value) {
        return;
    }

    if (get("compare_with") !== null && get("expanded") === null) {
        set("expanded", "true");
    }

    if (Boolean(get("expanded"))) {
        toggleExpand(false);
    }

    const compareWith = get("compare_with") as string;

    if (compareWith) {
        playerToCompareWith.value = rateableToLeaderboardRows(
            (await getRateable(compareWith)).data!,
        );
        playerToCompareWithRatingHistory.value =
            (await getRateableHistory("player", compareWith))?.data ?? [];

        await nextTick();

        renderRatingChart();
    }
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeydown);
    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
    }
});
</script>
