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
import { computed, reactive, ref } from "vue";
import MatchCard from "@/Components/MatchCard.vue";
import type { Match, Player } from "@/Types/core.ts";

const props = defineProps<{ players: Player[] }>();
const players = ref(props.players);

const emit = defineEmits(["playerClick"]);

const tournament = reactive({
    // Left Side
    leftR1M1: {
        id: "L1-1",
        round: "Round 1",
        player1: players.value.find(
            (p) => p.user_id === "57d301d409f2efcce834fc94",
        ),
        player2: players.value.find(
            (p) => p.user_id === "601d17c1d565030001440b8d",
        ),
        winner: null,
        isComplete: false,
        isLive: true,
        link: "https://twitch.tv/GeoGuessr",
    },
    leftR1M2: {
        id: "L1-2",
        round: "Round 2",
        player1: players.value.find(
            (p) => p.user_id === "5de7a59044d2a42f78156b33",
        ),
        player2: players.value.find(
            (p) => p.user_id === "603b1b0d5cdb1b0001bbf19e",
        ),
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
    leftR1M3: {
        id: "L1-3",
        round: "Round 3",
        player1: players.value.find(
            (p) => p.user_id === "5bf491faaac55b998458ed9a",
        ),
        player2: players.value.find(
            (p) => p.user_id === "5a973147afad0f2a68438531",
        ),
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
    leftR1M4: {
        id: "L1-4",
        round: "Round 4",
        player1: players.value.find(
            (p) => p.user_id === "5e2e983722bbda85a40e9009",
        ),
        player2: players.value.find(
            (p) => p.user_id === "57ebb537a52b273ab0162ed8",
        ),
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },

    // Right side
    rightR1M1: {
        id: "R1-1",
        round: "Round 5",
        player1: players.value.find(
            (p) => p.user_id === "5b51062a4010740f7cd91dd5",
        ),
        player2: players.value.find(
            (p) => p.user_id === "5e5fcc1326bbda5284e824cf",
        ),
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
    rightR1M2: {
        id: "R1-2",
        round: "Round 6",
        player1: players.value.find(
            (p) => p.user_id === "633a62ba560e8238dea97807",
        ),
        player2: players.value.find(
            (p) => p.user_id === "5c03eed1b5b94ba700403005",
        ),
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
    rightR1M3: {
        id: "R1-3",
        round: "Round 7",
        player1: players.value.find(
            (p) => p.user_id === "59d0b74bd8fe1d5b30651962",
        ),
        player2: players.value.find(
            (p) => p.user_id === "635c171d190621fb60d8bb08",
        ),
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
    rightR1M4: {
        id: "R1-4",
        round: "Round 8",
        player1: players.value.find(
            (p) => p.user_id === "55abc223ffb93d3658e4b76c",
        ),
        player2: players.value.find(
            (p) => p.user_id === "5b4899f5b56fe41a1831bba4",
        ),
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },

    // Quarter Finals
    leftQ1: {
        id: "LQ-1",
        round: "Quarter Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
    leftQ2: {
        id: "LQ-2",
        round: "Quarter Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
    rightQ1: {
        id: "RQ-1",
        round: "Quarter Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
    rightQ2: {
        id: "RQ-2",
        round: "Quarter Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },

    // Semi Finals
    leftSemi: {
        id: "LS",
        round: "Semi Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
    rightSemi: {
        id: "RS",
        round: "Semi Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },

    // Finals
    thirdPlace: {
        id: "TP",
        round: "3rd Place",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
    grandFinal: {
        id: "GF",
        round: "Grand Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
        link: "https://twitch.tv/GeoGuessr"
    },
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
