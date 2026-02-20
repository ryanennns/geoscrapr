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
                    <transition name="fade">
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
                    </transition>
                    <div
                        class="overflow-hidden transition-all duration-300"
                        :class="
                            showSearch
                                ? 'max-w-xs opacity-100 mr-8'
                                : 'max-w-0 opacity-0 mr-0 pointer-events-none'
                        "
                    >
                        <PlayerTeamSearch
                            @rowClicked="handleSelectPlayerToCompareWith"
                            @cleared="handleClearComparison"
                            placeholder="Compare with player..."
                            :show-teams="false"
                        />
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

                <div class="relative flex-1 min-h-0">
                    <div
                        class="absolute inset-0 flex items-start justify-center pt-10 z-10 pointer-events-none transition-opacity duration-300"
                        :class="internalLoading ? 'opacity-100' : 'opacity-0'"
                    >
                        <LoadingSpinner />
                    </div>
                    <div
                        v-show="props.ratingHistory.length > 0"
                        class="transition-opacity duration-300 h-full"
                        :class="internalLoading ? 'opacity-0' : 'opacity-100'"
                    >
                        <div
                            class="w-full transition-all duration-300"
                            :class="canvasWrapperClasses"
                        >
                            <div
                                class="transition-opacity duration-150 h-full"
                                :class="
                                    chartVisible ? 'opacity-100' : 'opacity-0'
                                "
                            >
                                <canvas ref="ratingChartCanvas" />
                            </div>
                        </div>
                    </div>
                    <ErrorMessage
                        class="mt-6 transition-opacity duration-300"
                        :class="internalLoading ? 'opacity-0' : 'opacity-100'"
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

const { getRateableHistory, getRateable } = useApiClient();
const { rateableToLeaderboardRows } = usePlayerUtils();
const { isMobile } = useBrowserUtils();
const { get, set, clear } = useUrlParams();
const { isDark } = useDarkMode();

const emit = defineEmits(["close"]);

const MIN_SPINNER_MS = 150;
let openTime = 0;
const internalLoading = ref<boolean>(true);

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
const chartVisible = ref<boolean>(true);
const toggleExpand = async (rerender: boolean = true) => {
    expanded.value = !expanded.value;
    expanded.value ? (daysToShow.value = 112) : (daysToShow.value = 14);
    expanded.value
        ? set("expanded", String(expanded.value))
        : clear("expanded") > clear("compare_with");

    playerToCompareWith.value = null;
    playerToCompareWithRatingHistory.value = [];

    if (rerender) {
        chartVisible.value = false;
        await new Promise<void>((resolve) => setTimeout(resolve, 150));
        renderRatingChart();
        await nextTick();
        chartVisible.value = true;
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

    chartVisible.value = false;
    await new Promise<void>((resolve) => setTimeout(resolve, 150));
    renderRatingChart();
    await nextTick();
    chartVisible.value = true;
};

const handleClearComparison = async () => {
    playerToCompareWith.value = null;
    playerToCompareWithRatingHistory.value = [];
    clear("compare_with");

    chartVisible.value = false;
    await new Promise<void>((resolve) => setTimeout(resolve, 150));
    renderRatingChart();
    await nextTick();
    chartVisible.value = true;
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

watch(
    () => props.loading,
    (loading) => {
        if (!loading && props.showModal) {
            const remaining = MIN_SPINNER_MS - (Date.now() - openTime);
            setTimeout(
                () => {
                    internalLoading.value = false;
                },
                Math.max(0, remaining),
            );
        }
    },
);

const loadingMatchHistory = ref<boolean>(false);
const matchHistory = ref<MatchHistory[]>([]);
watch(
    () => props.showModal,
    async (show) => {
        if (show) {
            openTime = Date.now();
            internalLoading.value = true;
            if (!props.loading) {
                setTimeout(
                    () => {
                        internalLoading.value = false;
                    },
                    Math.random() * 500 + MIN_SPINNER_MS,
                );
            }
        } else {
            internalLoading.value = true;
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

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
