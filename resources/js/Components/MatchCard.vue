<template>
    <div class="relative">
        <div v-if="match.is_live" class="absolute -top-1 -right-1 z-10">
            <span class="relative flex h-3 w-3 sm:h-4 sm:w-4">
                <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"
                ></span>
                <span
                    class="relative inline-flex rounded-full h-3 w-3 sm:h-4 sm:w-4 bg-red-500 shadow-lg"
                ></span>
            </span>
        </div>

        <div class="flex justify-between items-center mb-2 sm:mb-3">
            <div
                class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide"
            >
                {{ match.round }}
            </div>

            <div
                v-if="match.scheduled_at && !match.is_live && !match.winner"
                class="text-xs text-gray-500 dark:text-gray-400 font-medium"
            >
                {{
                    new Date(match.scheduled_at).toLocaleString([], {
                        month: "short",
                        day: "2-digit",
                        hour: "2-digit",
                        minute: "2-digit",
                    })
                }}
            </div>
            <div
                v-else-if="!match.is_live"
                class="text-xs text-gray-500 dark:text-gray-400 font-medium"
            >
                --:--
            </div>
        </div>

        <div
            :class="[
                'relative border rounded-xl shadow-md hover:shadow-lg transition-all duration-300',
                'border-gray-200 dark:border-gray-600',
                'bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900',
                'p-2 sm:p-3 lg:p-4 space-y-3 sm:space-y-4',
                match.is_live
                    ? 'ring-2 ring-red-500/20 border-red-200 dark:border-red-800'
                    : '',
                customClass,
            ]"
        >
            <div class="flex items-center justify-between min-w-0">
                <!-- Player One -->
                <div
                    class="flex flex-col items-center space-y-1 sm:space-y-2 flex-1 min-w-0"
                >
                    <div
                        class="relative w-full max-w-[4rem] sm:max-w-[5rem] lg:max-w-[4rem] aspect-square"
                    >
                        <div
                            class="w-full h-full overflow-hidden rounded-full ring-2"
                            :class="
                                isP1Winner
                                    ? 'ring-green-500 dark:ring-green-400'
                                    : 'ring-gray-200 dark:ring-gray-700'
                            "
                        >
                            <img
                                v-show="playerOneImageSlug !== 'finalist-tbd'"
                                :src="`images/${playerOneImageSlug}.webp`"
                                alt=""
                                class="h-full w-full object-cover scale-225 object-[50%_100%]"
                                loading="lazy"
                            />

                            <Flag
                                v-if="props.match.player_one?.country_code"
                                :country-code="
                                    props.match.player_one.country_code
                                "
                                class="absolute bottom-1 right-1 translate-x-1/4 translate-y-1/4 rounded-sm shadow-md"
                            />
                        </div>
                    </div>
                    <div
                        class="text-xs sm:text-sm font-medium text-gray-800 dark:text-gray-200 text-center leading-tight px-1 min-w-0 w-full"
                    >
                        <span class="block truncate">{{ playerOneName }}</span>
                    </div>
                </div>

                <div
                    class="flex flex-col items-center justify-center px-2 sm:px-3 flex-shrink-0"
                >
                    <div
                        class="text-xs font-bold text-gray-600 dark:text-gray-400 px-1.5 sm:px-2 py-1 rounded-full"
                    >
                        VS
                    </div>
                </div>

                <!-- Player Two -->
                <div
                    class="flex flex-col items-center space-y-1 sm:space-y-2 flex-1 min-w-0"
                >
                    <div
                        class="relative w-full max-w-[4rem] sm:max-w-[5rem] lg:max-w-[4rem] aspect-square"
                    >
                        <div
                            class="w-full h-full overflow-hidden rounded-full ring-2"
                            :class="
                                isP2Winner
                                    ? 'ring-green-500 dark:ring-green-400'
                                    : 'ring-gray-200 dark:ring-gray-700'
                            "
                        >
                            <img
                                v-show="playerTwoImageSlug !== 'finalist-tbd'"
                                :src="`images/${playerTwoImageSlug}.webp`"
                                alt=""
                                class="h-full w-full object-cover scale-225 object-[40%_40%]"
                                loading="lazy"
                            />

                            <Flag
                                v-if="props.match.player_two?.country_code"
                                :country-code="
                                    props.match.player_two.country_code
                                "
                                class="absolute bottom-1 right-1 translate-x-1/4 translate-y-1/4 rounded-sm shadow-md"
                            />
                        </div>
                    </div>
                    <div
                        class="text-xs sm:text-sm font-medium text-gray-800 dark:text-gray-200 text-center leading-tight px-1 min-w-0 w-full"
                    >
                        <span class="block truncate">{{ playerTwoName }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="match.winner"
            class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-sm"
        >
            {{ match.player_one_score }} - {{ match.player_two_score }}
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Match, Player } from "@/Types/core.ts";
import { computed } from "vue";
import Flag from "@/Components/Flag.vue";

const props = defineProps<{
    match: Match;
    customClass?: string;
}>();

defineEmits<{
    (e: "playerClick", player: Player): void;
}>();

const playerOneName = computed(() => props.match.player_one?.name || "TBD");
const playerTwoName = computed(() => props.match.player_two?.name || "TBD");

const playerOneImageSlug = computed(
    () =>
        "finalist-" +
        (props.match.player_one?.name ?? "tbd")
            .toLowerCase()
            .replace(/ /g, "-"),
);
const playerTwoImageSlug = computed(
    () =>
        "finalist-" +
        (props.match.player_two?.name ?? "tbd")
            .toLowerCase()
            .replace(/ /g, "-"),
);

// Winner ring logic
const isP1Winner = computed(
    () =>
        !!props.match.winner?.id &&
        props.match.winner?.id === props.match.player_one?.id,
);
const isP2Winner = computed(
    () =>
        !!props.match.winner?.id &&
        props.match.winner?.id === props.match.player_two?.id,
);
</script>
