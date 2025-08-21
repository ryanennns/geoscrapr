<template>
    <div>
        <span
            v-if="match.isLive"
            class="absolute top-2 right-2 w-3 h-3 rounded-full bg-red-500 animate-ping"
        ></span>
        <span
            v-if="match.isLive"
            class="absolute top-2 right-2 w-3 h-3 rounded-full bg-red-500"
        ></span>

        <div class="text-xs font-medium text-gray-500 mb-2">
            {{ match.round }}
        </div>

        <div
            :class="[
                'border rounded-lg shadow-sm p-3 border-gray-300',
                customClass,
            ]"
        >
            <!-- Player 1 -->
            <div
                v-if="match.player_one"
                @click="$emit('playerClick', match.player_one)"
                :class="[
                    'flex items-center justify-between p-2 rounded cursor-pointer hover:bg-gray-50 transition-colors mb-1',
                    match.winner?.id === match.player_one.id
                        ? 'bg-green-100 border border-green-300'
                        : 'bg-gray-50',
                ]"
            >
                <div class="flex items-center space-x-2">
                    <Flag
                        :country-code="
                            match.player_one.country_code as CountryCode
                        "
                    />
                    <span
                        :class="[
                            'text-sm font-medium',
                            match.winner?.id === match.player_one.id
                                ? 'text-green-800'
                                : 'text-gray-700',
                        ]"
                    >
                        {{ match.player_one.name }}
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <span
                        v-if="match.score1 !== undefined"
                        class="text-sm font-bold"
                        >{{ match.score1 }}</span
                    >
                    <span class="text-xs text-gray-500"
                        >({{ match.player_one.rating ?? "N/A" }})</span
                    >
                </div>
            </div>

            <!-- Placeholder for Player 1 -->
            <div
                v-else
                class="flex items-center justify-between p-2 rounded bg-gray-100 mb-1"
            >
                <span class="text-sm text-gray-400">TBD</span>
            </div>

            <!-- VS Divider -->
            <div class="text-center text-xs text-gray-400 font-medium py-1">
                VS
            </div>

            <!-- Player 2 -->
            <div
                v-if="match.player_two"
                @click="$emit('playerClick', match.player_two)"
                :class="[
                    'flex items-center justify-between p-2 rounded cursor-pointer hover:bg-gray-50 transition-colors mt-1',
                    match.winner?.id === match.player_two.id
                        ? 'bg-green-100 border border-green-300'
                        : 'bg-gray-50',
                ]"
            >
                <div class="flex items-center space-x-2">
                    <Flag
                        :country-code="
                            match.player_two.country_code as CountryCode
                        "
                    />
                    <span
                        :class="[
                            'text-sm font-medium',
                            match.winner?.id === match.player_two.id
                                ? 'text-green-800'
                                : 'text-gray-700',
                        ]"
                    >
                        {{ match.player_two.name }}
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <span
                        v-if="match.score2 !== undefined"
                        class="text-sm font-bold"
                        >{{ match.score2 }}</span
                    >
                    <span class="text-xs text-gray-500"
                        >({{ match.player_two.rating ?? "N/A" }})</span
                    >
                </div>
            </div>

            <!-- Placeholder for Player 2 -->
            <div
                v-else
                class="flex items-center justify-between p-2 rounded bg-gray-100 mt-1"
            >
                <span class="text-sm text-gray-400">TBD</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Flag from "@/Components/Flag.vue";
import type { CountryCode } from "@/Composables/usePlayerUtils.ts";
import type { Match, Player } from "@/Types/core.ts";

defineProps<{
    match: Match;
    customClass?: string;
}>();

defineEmits<{
    (e: "playerClick", player: Player): void;
}>();
</script>
