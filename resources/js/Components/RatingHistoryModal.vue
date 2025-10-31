<template>
    <transition name="fade">
        <div
            v-if="props.showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/75"
            @click.self="onClose"
        >
            <div
                class="flex flex-col bg-white rounded-lg shadow-lg px-6 pt-6 pb-5 w-full max-w-2xl transition-all duration-300"
                :class="wrapperClasses"
            >
                <div class="flex justify-between items-start">
                    <span class="grow">
                        <span class="text-xl font-bold flex items-center mb-2">
                            <span
                                v-for="countryCode in props.leaderboardRow
                                    .countryCodes"
                            >
                                <Flag
                                    :country-code="countryCode"
                                    dimensions="120x90"
                                    class="mr-1"
                                    width="20"
                                    height="15"
                                />
                            </span>
                            <p class="">
                                {{ croppedName }}
                            </p>
                            <p class="font-light ml-1">
                                - {{ props.leaderboardRow.rating }}
                            </p>
                            <div
                                class="hidden sm:flex flex-wrap gap-2 items-center ml-4"
                            >
                                <RatingBadge
                                    v-show="props.leaderboardRow.moving_rating"
                                    label="Moving: "
                                    :text="`${props.leaderboardRow.moving_rating}`"
                                />
                                <RatingBadge
                                    v-show="props.leaderboardRow.no_move_rating"
                                    label="No Move: "
                                    :text="`${props.leaderboardRow.no_move_rating}`"
                                />
                                <RatingBadge
                                    v-show="props.leaderboardRow.nmpz_rating"
                                    label="NMPZ: "
                                    :text="`${props.leaderboardRow.nmpz_rating}`"
                                />
                            </div>
                        </span>

                        <span
                            class="flex items-center gap-2"
                            v-if="!props.leaderboardRow.players"
                        >
                            <p>
                                Overall Rank:
                                <span class="font-bold"
                                    >#{{ props.leaderboardRow.rank }}</span
                                >
                            </p>
                            —
                            <p
                                v-if="props.leaderboardRow.percentile"
                                class="font-bold"
                            >
                                P{{
                                    Math.floor(
                                        props.leaderboardRow.percentile * 100,
                                    ) / 100
                                }}
                            </p>
                        </span>

                        <span v-if="props.leaderboardRow.players" class="flex">
                            <a
                                :href="
                                    generateProfileUrl(
                                        props.leaderboardRow.players[0]
                                            ?.user_id,
                                    )
                                "
                                target="_blank"
                                class="mr-1"
                            >
                                <p
                                    class="text-gray-600 font-mono underline font-light"
                                >
                                    {{ props.leaderboardRow.players[0]?.name }}
                                </p>
                            </a>
                            &
                            <a
                                :href="
                                    generateProfileUrl(
                                        props.leaderboardRow.players[1]
                                            ?.user_id,
                                    )
                                "
                                target="_blank"
                                class="ml-1"
                            >
                                <p
                                    class="text-gray-600 font-mono underline font-light"
                                >
                                    {{ props.leaderboardRow.players[1]?.name }}
                                </p>
                            </a>
                        </span>
                        <div v-else class="flex items-center mb-2 w-full">
                            <a
                                :href="
                                    generateProfileUrl(
                                        props.leaderboardRow.geoGuessrId,
                                    )
                                "
                                target="_blank"
                            >
                                <p
                                    class="text-gray-600 font-mono underline font-light"
                                >
                                    {{ props.leaderboardRow.geoGuessrId }}
                                </p>
                            </a>

                            <div
                                v-if="
                                    matchHistory.length &&
                                    !isMobile &&
                                    !loadingMatchHistory &&
                                    expanded
                                "
                                class="flex gap-1 ml-auto"
                            >
                                Recent Matches:
                                <div v-for="match in matchHistory">
                                    <a
                                        :href="`https://geoguessr.com/duels/${match.id}`"
                                        target="_blank"
                                    >
                                        <div
                                            v-if="
                                                match.winner ===
                                                leaderboardRow.id
                                            "
                                        >
                                            🟢
                                        </div>
                                        <div v-else>🔴</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </span>
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
import Flag from "@/Components/Flag.vue";
import ErrorMessage from "@/Components/ErrorMessage.vue";
import { usePlayerUtils } from "@/Composables/usePlayerUtils.ts";
import type { LeaderboardRow, RatingChange } from "@/Types/core.ts";
import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import RatingBadge from "@/Components/RatingBadge.vue";
import ExpandContractButton from "@/Components/ExpandContractButton.vue";
import { useBrowserUtils } from "@/Composables/useBrowserUtils.ts";
import CloseButton from "@/Components/CloseButton.vue";
import { useUrlParams } from "@/Composables/useUrlParams.ts";
import { type MatchHistory, useApiClient } from "@/Composables/useApiClient.ts";
import {
    createRatingChart,
    getRatingHistoryChartData,
} from "@/modalChartUtils.ts";

interface Props {
    showModal: boolean;
    leaderboardRow: LeaderboardRow;
    ratingHistory: RatingChange[];
    loading: boolean;
}
const props = defineProps<Props>();

const { getMatchHistory } = useApiClient();
const { isMobile } = useBrowserUtils();
const { generateProfileUrl } = usePlayerUtils();
const { get, set, clear } = useUrlParams();

const emit = defineEmits(["close"]);

const allowedNameLength = computed<number>(() => {
    let numberOfNullRatings = 0;

    if (!props.leaderboardRow.moving_rating) {
        numberOfNullRatings += 1;
    }

    if (!props.leaderboardRow.no_move_rating) {
        numberOfNullRatings += 1;
    }

    if (!props.leaderboardRow.nmpz_rating) {
        numberOfNullRatings += 1;
    }

    return 13 + numberOfNullRatings * 2;
});

const croppedName = computed<string>(() => {
    if (expanded.value) {
        return props.leaderboardRow.name.trim();
    }

    if (props.leaderboardRow.name.trim().length <= allowedNameLength.value) {
        return props.leaderboardRow.name.trim();
    }

    return (
        props.leaderboardRow.name.trim().slice(0, allowedNameLength.value - 3) +
        "..."
    );
});

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
    if (range <= 400) return 250;
    return Math.ceil(range / 4 / 50) * 50;
};

const expanded = ref<boolean>(false);
const toggleExpand = () => {
    expanded.value = !expanded.value;
    expanded.value ? (daysToShow.value = 56) : (daysToShow.value = 14);
    expanded.value
        ? set("expanded", String(expanded.value))
        : clear("expanded");

    renderRatingChart();
};

const wrapperClasses = computed<string>(() =>
    isMobile.value
        ? "h-360px"
        : expanded.value
          ? "max-w-7xl h-[80vh]"
          : "max-w-2xl h-[40vh]",
);
const canvasWrapperClasses = computed<string>(() =>
    isMobile.value ? "w-full h-60" : expanded.value ? "h-[65vh]" : "h-60",
);

const ratingChartInstance = ref<Chart | null>(null);
const onClose = () => {
    emit("close");

    clear("expanded");

    if (ratingChartInstance.value) {
        setTimeout(() => {
            ratingChartInstance.value?.destroy();
            ratingChartInstance.value = null;
        }, 300);
    }

    expanded.value = false;
    daysToShow.value = 14;
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

    const { labels, data } = getRatingHistoryChartData({
        daysToShow: daysToShow.value,
        ratingHistory: props.ratingHistory,
        leaderboardRow: props.leaderboardRow,
    });

    if (data.length === 0) {
        return;
    }

    const validData = data.filter((value) => value !== null);
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
        data,
        gradient,
        yMin,
        yMax,
        step,
        daysToShow: daysToShow.value,
    });
};

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
    await nextTick();
    if (!!get("expanded")) {
        toggleExpand();
    }
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeydown);
    if (ratingChartInstance.value) {
        ratingChartInstance.value.destroy();
    }
});
</script>
