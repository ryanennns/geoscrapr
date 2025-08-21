<template>
    <div>
        <div class="bg-white p-4 md:p-6 rounded-xl shadow-md">
            <div
                class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 gap-3"
            >
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                    GeoGuessr World Cup 2025
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
                                class="relative p-2 space-y-1 border-l-4 border-blue-300 pl-2 bg-blue-50 rounded-lg"
                            >
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Left Side - Quarter Finals -->
                        <div class="space-y-16">
                            <div
                                v-for="match in leftQuarters"
                                :key="match.id"
                                class="p-2 space-y-1 border-l-4 border-green-300 pl-2 bg-green-50 rounded-lg"
                            >
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Left Semi Final -->
                        <div>
                            <div
                                class="p-2 space-y-1 border-l-4 border-purple-300 pl-2 bg-purple-50 rounded-lg"
                            >
                                <MatchCard
                                    :match="leftSemi"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Finals Column -->
                        <div class="space-y-8">
                            <!-- Grand Final -->
                            <div
                                class="p-2 space-y-1 border-l-4 border-r-4 border-yellow-400 px-2 bg-yellow-50 rounded-lg"
                            >
                                <MatchCard
                                    :match="grandFinal"
                                    @player-click="handlePlayerClick"
                                    class="border-yellow-300 bg-yellow-50 shadow-lg"
                                />
                            </div>

                            <!-- Third Place Match -->
                            <div
                                class="p-2 space-y-1 border-r-4 border-l-4 border-orange-400 px-2 bg-orange-50 rounded-lg"
                            >
                                <MatchCard
                                    :match="thirdPlaceMatch"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Right Semi Final -->
                        <div>
                            <div
                                class="p-2 space-y-1 border-r-4 border-purple-300 pr-2 bg-purple-50 rounded-lg"
                            >
                                <MatchCard
                                    :match="rightSemi"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Right Side - Quarter Finals -->
                        <div class="space-y-16">
                            <div
                                v-for="match in rightQuarters"
                                :key="match.id"
                                class="p-2 space-y-1 border-r-4 border-green-300 pr-2 bg-green-50 rounded-lg"
                            >
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Right Side - Round 1 -->
                        <div class="space-y-8">
                            <div
                                v-for="match in rightSide"
                                :key="match.id"
                                class="p-2 space-y-1 border-r-4 border-blue-300 pr-2 bg-blue-50 rounded-lg"
                            >
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
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

console.log(JSON.parse(JSON.stringify(tournament)));

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
