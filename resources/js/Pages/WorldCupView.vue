<template>
    <div class="mt-6 md:mt-10">
        <div class="bg-white p-4 md:p-6 rounded-xl shadow-md">
            <div
                class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 gap-3"
            >
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                    GeoGuessr World Cup 2025
                </h2>
                <div class="flex flex-wrap gap-2 sm:gap-4">
                    <button
                        @click="simulateRound"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        :disabled="isComplete"
                    >
                        {{
                            isComplete
                                ? "Tournament Complete"
                                : "Simulate Next Round"
                        }}
                    </button>
                    <button
                        @click="resetTournament"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <div class="min-w-[1200px] p-4">
                    <!-- Tournament Bracket Grid -->
                    <div class="grid grid-cols-7 gap-4 items-center">
                        <!-- Left Side - Round 1 -->
                        <div class="space-y-8">
                            <div
                                v-for="match in leftSideRound1"
                                :key="match.id"
                                class="space-y-1"
                            >
                                <div
                                    class="text-xs font-medium text-gray-500 mb-2"
                                >
                                    {{ match.round }}
                                </div>
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Left Side - Quarter Finals -->
                        <div class="space-y-16 mt-8">
                            <div
                                v-for="match in leftQuarters"
                                :key="match.id"
                                class="space-y-1"
                            >
                                <div
                                    class="text-xs font-medium text-gray-500 mb-2"
                                >
                                    {{ match.round }}
                                </div>
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Left Semi Final -->
                        <div class="mt-24">
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-medium text-gray-500 mb-2 text-center"
                                >
                                    {{ leftSemi.round }}
                                </div>
                                <MatchCard
                                    :match="leftSemi"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Finals Column -->
                        <div class="space-y-8">
                            <!-- Grand Final -->
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-medium text-gray-500 mb-2 text-center"
                                >
                                    {{ grandFinal.round }}
                                </div>
                                <MatchCard
                                    :match="grandFinal"
                                    @player-click="handlePlayerClick"
                                    class="border-yellow-300 bg-yellow-50 shadow-lg"
                                />
                            </div>

                            <!-- Third Place Match -->
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-medium text-gray-500 mb-2 text-center"
                                >
                                    {{ thirdPlaceMatch.round }}
                                </div>
                                <MatchCard
                                    :match="thirdPlaceMatch"
                                    @player-click="handlePlayerClick"
                                    class="border-orange-200 bg-orange-50"
                                />
                            </div>
                        </div>

                        <!-- Right Semi Final -->
                        <div class="mt-24">
                            <div class="space-y-1">
                                <div
                                    class="text-xs font-medium text-gray-500 mb-2 text-center"
                                >
                                    {{ rightSemi.round }}
                                </div>
                                <MatchCard
                                    :match="rightSemi"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Right Side - Quarter Finals -->
                        <div class="space-y-16 mt-8">
                            <div
                                v-for="match in rightQuarters"
                                :key="match.id"
                                class="space-y-1"
                            >
                                <div
                                    class="text-xs font-medium text-gray-500 mb-2"
                                >
                                    {{ match.round }}
                                </div>
                                <MatchCard
                                    :match="match"
                                    @player-click="handlePlayerClick"
                                />
                            </div>
                        </div>

                        <!-- Right Side - Round 1 -->
                        <div class="space-y-8">
                            <div
                                v-for="match in rightSideRound1"
                                :key="match.id"
                                class="space-y-1"
                            >
                                <div
                                    class="text-xs font-medium text-gray-500 mb-2"
                                >
                                    {{ match.round }}
                                </div>
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
import type { Player } from "@/Types/core.ts";

const props = defineProps<{
    players: Player[];
}>();

const players = ref(props.players);

// MatchCard Component
interface Match {
    id: string;
    round: string;
    player1: Player | null;
    player2: Player | null;
    winner: Player | null;
    isComplete: boolean;
    score1?: number | undefined;
    score2?: number | undefined;
}

const emit = defineEmits(["playerClick"]);

const tournament = reactive({
    leftR1M1: {
        id: "L1-1",
        round: "Round 1",
        player1: players.value.find(p => p.user_id === '57d301d409f2efcce834fc94'),
        player2: players.value.find(p => p.user_id === '601d17c1d565030001440b8d'),
        winner: null,
        isComplete: false,
    },
    leftR1M2: {
        id: "L1-2",
        round: "Round 2",
        player1: players.value.find(p => p.user_id === '5de7a59044d2a42f78156b33'),
        player2: players.value.find(p => p.user_id === '603b1b0d5cdb1b0001bbf19e'),
        winner: null,
        isComplete: false,
    },
    leftR1M3: {
        id: "L1-3",
        round: "Round 3",
        player1: players.value.find(p => p.user_id === '5bf491faaac55b998458ed9a'),
        player2: players.value.find(p => p.user_id === '5a973147afad0f2a68438531'),
        winner: null,
        isComplete: false,
    },
    leftR1M4: {
        id: "L1-4",
        round: "Round 4",
        player1: players.value.find(p => p.user_id === '5e2e983722bbda85a40e9009'),
        player2: players.value.find(p => p.user_id === '57ebb537a52b273ab0162ed8'),
        winner: null,
        isComplete: false,
    },

    rightR1M1: {
        id: "R1-1",
        round: "Round 5",
        player1: players.value.find(p => p.user_id === '5b51062a4010740f7cd91dd5'),
        player2: players.value.find(p => p.user_id === '5e5fcc1326bbda5284e824cf'),
        winner: null,
        isComplete: false,
    },
    rightR1M2: {
        id: "R1-2",
        round: "Round 6",
        player1: players.value.find(p => p.user_id === '633a62ba560e8238dea97807'),
        player2: players.value.find(p => p.user_id === '5c03eed1b5b94ba700403005'),
        winner: null,
        isComplete: false,
    },
    rightR1M3: {
        id: "R1-3",
        round: "Round 7",
        player1: players.value.find(p => p.user_id === '59d0b74bd8fe1d5b30651962'),
        player2: players.value.find(p => p.user_id === '635c171d190621fb60d8bb08'),
        winner: null,
        isComplete: false,
    },
    rightR1M4: {
        id: "R1-4",
        round: "Round 8",
        player1: players.value.find(p => p.user_id === '55abc223ffb93d3658e4b76c'),
        player2: players.value.find(p => p.user_id === '5b4899f5b56fe41a1831bba4'),
        winner: null,
        isComplete: false,
    },

    // Quarter Finals
    leftQ1: {
        id: "LQ-1",
        round: "Quarter Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
    },
    leftQ2: {
        id: "LQ-2",
        round: "Quarter Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
    },
    rightQ1: {
        id: "RQ-1",
        round: "Quarter Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
    },
    rightQ2: {
        id: "RQ-2",
        round: "Quarter Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
    },

    // Semi Finals
    leftSemi: {
        id: "LS",
        round: "Semi Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
    },
    rightSemi: {
        id: "RS",
        round: "Semi Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
    },

    // Finals
    thirdPlace: {
        id: "TP",
        round: "3rd Place",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
    },
    grandFinal: {
        id: "GF",
        round: "Grand Final",
        player1: null,
        player2: null,
        winner: null,
        isComplete: false,
    },
} as Record<string, Match>);

const leftSideRound1 = computed(() => [
    tournament.leftR1M1,
    tournament.leftR1M2,
    tournament.leftR1M3,
    tournament.leftR1M4,
]);

const rightSideRound1 = computed(() => [
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

const isComplete = computed(() => tournament.grandFinal.isComplete);

const simulateMatch = (match: Match) => {
    if (!match.player1 || !match.player2 || match.isComplete) return;

    // Simple simulation based on rating with some randomness
    const rating1 = match.player1.rating ?? 0;
    const rating2 = match.player2.rating ?? 0;
    const expected1 = 1 / (1 + Math.pow(10, (rating2 - rating1) / 400));

    const random = Math.random();
    const winner = random < expected1 ? match.player1 : match.player2;

    match.winner = winner;
    match.isComplete = true;

    // Add scores for display
    if (winner === match.player1) {
        match.score1 = Math.floor(Math.random() * 3) + 3; // 3-5
        match.score2 = Math.floor(Math.random() * match.score1); // 0 to score1-1
    } else {
        match.score2 = Math.floor(Math.random() * 3) + 3; // 3-5
        match.score1 = Math.floor(Math.random() * match.score2); // 0 to score2-1
    }
};

const simulateRound = () => {
    // Round 1
    if (!tournament.leftR1M1.isComplete) {
        leftSideRound1.value.forEach(simulateMatch);
        rightSideRound1.value.forEach(simulateMatch);

        // Advance winners to quarters
        tournament.leftQ1.player1 = tournament.leftR1M1.winner;
        tournament.leftQ1.player2 = tournament.leftR1M2.winner;
        tournament.leftQ2.player1 = tournament.leftR1M3.winner;
        tournament.leftQ2.player2 = tournament.leftR1M4.winner;

        tournament.rightQ1.player1 = tournament.rightR1M1.winner;
        tournament.rightQ1.player2 = tournament.rightR1M2.winner;
        tournament.rightQ2.player1 = tournament.rightR1M3.winner;
        tournament.rightQ2.player2 = tournament.rightR1M4.winner;
        return;
    }

    // Quarter Finals
    if (!tournament.leftQ1.isComplete) {
        leftQuarters.value.forEach(simulateMatch);
        rightQuarters.value.forEach(simulateMatch);

        // Advance winners to semis
        tournament.leftSemi.player1 = tournament.leftQ1.winner;
        tournament.leftSemi.player2 = tournament.leftQ2.winner;
        tournament.rightSemi.player1 = tournament.rightQ1.winner;
        tournament.rightSemi.player2 = tournament.rightQ2.winner;
        return;
    }

    // Semi Finals
    if (!tournament.leftSemi.isComplete) {
        simulateMatch(tournament.leftSemi);
        simulateMatch(tournament.rightSemi);

        // Set up finals
        tournament.grandFinal.player1 = tournament.leftSemi.winner;
        tournament.grandFinal.player2 = tournament.rightSemi.winner;

        // Set up third place match (losers of semis)
        tournament.thirdPlace.player1 =
            tournament.leftSemi.player1 === tournament.leftSemi.winner
                ? tournament.leftSemi.player2
                : tournament.leftSemi.player1;
        tournament.thirdPlace.player2 =
            tournament.rightSemi.player1 === tournament.rightSemi.winner
                ? tournament.rightSemi.player2
                : tournament.rightSemi.player1;
        return;
    }

    // Finals
    if (!tournament.grandFinal.isComplete) {
        simulateMatch(tournament.thirdPlace);
        simulateMatch(tournament.grandFinal);
    }
};

const resetTournament = () => {
    Object.keys(tournament).forEach((key) => {
        const match = tournament[key];
        match.winner = null;
        match.isComplete = false;
        match.score1 = undefined;
        match.score2 = undefined;

        // Reset players for later rounds
        if (
            key.includes("Q") ||
            key.includes("Semi") ||
            key.includes("third") ||
            key.includes("grand")
        ) {
            match.player1 = null;
            match.player2 = null;
        }
    });
};

const handlePlayerClick = (player: Player): void => {
    console.log('snickers', player.name)
    emit("playerClick", player);
};
</script>
