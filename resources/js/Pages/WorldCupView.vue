<template>
    <div class="bg-gray-50 dark:bg-gray-800 min-h-screen">
        <div class="bg-white dark:bg-gray-800 p-4 md:p-6">
            <div class="flex flex-col text-center mb-4 gap-3">
                <h2
                    class="text-xl md:text-2xl font-bold text-gray-800 dark:text-gray-100"
                >
                    🏆 GeoGuessr World Cup 2025 🏆
                </h2>
            </div>

            <div class="overflow-x-auto">
                <div class="min-w-[1200px] p-4">
                    <div class="grid grid-cols-7 gap-4 items-center">
                        <!-- Left Side - Round 1 -->
                        <div class="space-y-8">
                            <div
                                v-for="match in leftSide"
                                :key="match.id"
                                class="relative p-2 space-y-1 border-l-4 border-blue-300 dark:border-blue-500/40 pl-2 bg-blue-50 dark:bg-blue-500/10 rounded-lg"
                            >
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
                                    class="text-gray-800 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <!-- Left Side - Quarter Finals -->
                        <div class="space-y-16">
                            <div
                                v-for="match in leftQuarters"
                                :key="match.id"
                                class="p-2 space-y-1 border-l-4 border-green-300 dark:border-green-500/40 pl-2 bg-green-50 dark:bg-green-500/10 rounded-lg"
                            >
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
                                    class="text-gray-800 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <!-- Left Semi Final -->
                        <div>
                            <div
                                class="p-2 space-y-1 border-l-4 border-purple-300 dark:border-purple-500/40 pl-2 bg-purple-50 dark:bg-purple-500/10 rounded-lg"
                            >
                                <MatchCard
                                    :match="leftSemi"
                                    @player-click="handlePlayerClick"
                                    class="text-gray-800 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <!-- Finals Column -->
                        <div class="space-y-8">
                            <!-- Grand Final -->
                            <div
                                class="p-2 space-y-1 border-l-4 border-r-4 border-yellow-400 dark:border-yellow-400/60 px-2 bg-yellow-50 dark:bg-yellow-400/10 rounded-lg"
                            >
                                <MatchCard
                                    :match="grandFinal"
                                    @player-click="handlePlayerClick"
                                    class="border-yellow-300 dark:border-yellow-400/40 bg-yello-50 shadow-lg text-gray-800 dark:text-gray-100"
                                />
                            </div>

                            <!-- Third Place Match -->
                            <div
                                class="p-2 space-y-1 border-r-4 border-l-4 border-orange-400 dark:border-orange-400/60 px-2 bg-orange-50 dark:bg-orange-400/10 rounded-lg"
                            >
                                <MatchCard
                                    :match="thirdPlaceMatch"
                                    @player-click="handlePlayerClick"
                                    class="text-gray-800 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <!-- Right Semi Final -->
                        <div>
                            <div
                                class="p-2 space-y-1 border-r-4 border-purple-300 dark:border-purple-500/40 pr-2 bg-purple-50 dark:bg-purple-500/10 rounded-lg"
                            >
                                <MatchCard
                                    :match="rightSemi"
                                    @player-click="handlePlayerClick"
                                    class="text-gray-800 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <!-- Right Side - Quarter Finals -->
                        <div class="space-y-16">
                            <div
                                v-for="match in rightQuarters"
                                :key="match.id"
                                class="p-2 space-y-1 border-r-4 border-green-300 dark:border-green-500/40 pr-2 bg-green-50 dark:bg-green-500/10 rounded-lg"
                            >
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
                                    class="text-gray-800 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <!-- Right Side - Round 1 -->
                        <div class="space-y-8">
                            <div
                                v-for="match in rightSide"
                                :key="match.id"
                                class="p-2 space-y-1 border-r-4 border-blue-300 dark:border-blue-500/40 pr-2 bg-blue-50 dark:bg-blue-500/10 rounded-lg"
                            >
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
                                    class="text-gray-800 dark:text-gray-100"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, reactive } from "vue";
import MatchCard from "@/Components/MatchCard.vue";
import type { Match } from "@/Types/core.ts";

const props = defineProps<{ matches: Match[] }>();
const emit = defineEmits(["playerClick"]);

const tournament = reactive({
    // Left Side
    leftR1M1: props.matches[0],
    leftR1M2: props.matches[1],
    leftR1M3: props.matches[2],
    leftR1M4: props.matches[3],

    // Right side
    rightR1M1: props.matches[4],
    rightR1M2: props.matches[5],
    rightR1M3: props.matches[6],
    rightR1M4: props.matches[7],

    // Quarter Finals
    leftQ1: props.matches[8],
    leftQ2: props.matches[9],
    rightQ1: props.matches[10],
    rightQ2: props.matches[11],

    // Semi Finals
    leftSemi: props.matches[12],
    rightSemi: props.matches[13],

    // Finals
    thirdPlace: props.matches[14],
    grandFinal: props.matches[15],
} as Record<string, Match>);

const leftSide = computed(() => [
    tournament.leftR1M1,
    tournament.leftR1M2,
    tournament.leftR1M3,
    tournament.leftR1M4,
]);
const rightSide = computed(() => [
    tournament.rightR1M1,
    tournament.rightR1M2,
    tournament.rightR1M3,
    tournament.rightR1M4,
]);
const leftQuarters = computed(() => [tournament.leftQ1, tournament.leftQ2]);
const rightQuarters = computed(() => [tournament.rightQ1, tournament.rightQ2]);
const leftSemi = computed(() => tournament.leftSemi);
const rightSemi = computed(() => tournament.rightSemi);
const thirdPlaceMatch = computed(() => tournament.thirdPlace);
const grandFinal = computed(() => tournament.grandFinal);

const handlePlayerClick = () => [];
</script>
