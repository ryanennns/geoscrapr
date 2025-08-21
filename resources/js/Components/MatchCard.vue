<template>
    <div :class="['border rounded-lg shadow-sm p-3', customClass]">
        <!-- Player 1 -->
        <div
            v-if="match.player1"
            @click="$emit('playerClick', match.player1)"
            :class="[
        'flex items-center justify-between p-2 rounded cursor-pointer hover:bg-gray-50 transition-colors mb-1',
        match.winner?.id === match.player1.id ? 'bg-green-100 border border-green-300' : 'bg-gray-50'
      ]"
        >
            <div class="flex items-center space-x-2">
                <Flag :country-code="match.player1.country_code as CountryCode" />
                <span :class="['text-sm font-medium', match.winner?.id === match.player1.id ? 'text-green-800' : 'text-gray-700']">
          {{ match.player1.name }}
        </span>
            </div>
            <div class="flex items-center space-x-2">
                <span v-if="match.score1 !== undefined" class="text-sm font-bold">{{ match.score1 }}</span>
                <span class="text-xs text-gray-500">({{ match.player1.rating ?? 'N/A' }})</span>
            </div>
        </div>

        <!-- Placeholder for Player 1 -->
        <div v-else class="flex items-center justify-between p-2 rounded bg-gray-100 mb-1">
            <span class="text-sm text-gray-400">TBD</span>
        </div>

        <!-- VS Divider -->
        <div class="text-center text-xs text-gray-400 font-medium py-1">VS</div>

        <!-- Player 2 -->
        <div
            v-if="match.player2"
            @click="$emit('playerClick', match.player2)"
            :class="[
        'flex items-center justify-between p-2 rounded cursor-pointer hover:bg-gray-50 transition-colors mt-1',
        match.winner?.id === match.player2.id ? 'bg-green-100 border border-green-300' : 'bg-gray-50'
      ]"
        >
            <div class="flex items-center space-x-2">
                <Flag :country-code="match.player2.country_code as CountryCode" />
                <span :class="['text-sm font-medium', match.winner?.id === match.player2.id ? 'text-green-800' : 'text-gray-700']">
          {{ match.player2.name }}
        </span>
            </div>
            <div class="flex items-center space-x-2">
                <span v-if="match.score2 !== undefined" class="text-sm font-bold">{{ match.score2 }}</span>
                <span class="text-xs text-gray-500">({{ match.player2.rating ?? 'N/A' }})</span>
            </div>
        </div>

        <!-- Placeholder for Player 2 -->
        <div v-else class="flex items-center justify-between p-2 rounded bg-gray-100 mt-1">
            <span class="text-sm text-gray-400">TBD</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import Flag from "@/Components/Flag.vue";
import type {CountryCode} from "@/Composables/usePlayerUtils.ts";
import type {Player} from "@/Types/core.ts";

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

defineProps<{
    match: Match;
    customClass?: string;
}>();

defineEmits<{
    (e: "playerClick", player: Player): void;
}>();
</script>
