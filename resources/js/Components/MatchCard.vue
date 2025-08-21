<template>
    <div class="relative">
        <!-- LIVE dot -->
        <span
            v-if="match.is_live"
            class="absolute top-1 right-2 w-3 h-3 rounded-full bg-red-500 animate-ping"
            aria-hidden="true"
        ></span>
        <span
            v-if="match.is_live"
            class="absolute top-1 right-2 w-3 h-3 rounded-full bg-red-500"
            aria-hidden="true"
        ></span>
        <span v-if="match.is_live" class="sr-only">Live</span>

        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
            {{ match.round }}
        </div>

        <div
            :class="[
                // card shell
                'border rounded-lg shadow-sm p-3',
                'border-gray-300 dark:border-gray-700',
                'bg-white dark:bg-gray-800',
                customClass,
            ]"
        >
            <!-- Player 1 -->
            <div
                v-if="match.player_one"
                @click="$emit('playerClick', match.player_one)"
                :class="[
                    'flex items-center justify-between p-2 rounded cursor-pointer transition-colors mb-1',
                    // hover states
                    'hover:bg-gray-50 dark:hover:bg-gray-700/50',
                    // winner vs default styling
                    match.winner?.id === match.player_one.id
                        ? 'bg-green-100 border border-green-300 dark:bg-green-900/20 dark:border-green-500/40'
                        : 'bg-gray-50 dark:bg-gray-700/40 dark:border-transparent',
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
                                ? 'text-green-800 dark:text-green-200'
                                : 'text-gray-700 dark:text-gray-200',
                        ]"
                    >
                        {{ match.player_one.name }}
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <span
                        v-if="match.score1 !== undefined"
                        class="text-sm font-bold text-gray-900 dark:text-gray-100"
                        >{{ match.score1 }}</span
                    >
                    <span class="text-xs text-gray-500 dark:text-gray-400"
                        >({{ match.player_one.rating ?? "N/A" }})</span
                    >
                </div>
            </div>

            <!-- Placeholder for Player 1 -->
            <div
                v-else
                class="flex items-center justify-between p-2 rounded bg-gray-100 dark:bg-gray-700/40 mb-1"
            >
                <span class="text-sm text-gray-400 dark:text-gray-500"
                    >TBD</span
                >
            </div>

            <!-- VS Divider -->
            <div
                class="text-center text-xs text-gray-400 dark:text-gray-500 font-medium py-1"
            >
                VS
            </div>

            <!-- Player 2 -->
            <div
                v-if="match.player_two"
                @click="$emit('playerClick', match.player_two)"
                :class="[
                    'flex items-center justify-between p-2 rounded cursor-pointer transition-colors mt-1',
                    'hover:bg-gray-50 dark:hover:bg-gray-700/50',
                    match.winner?.id === match.player_two.id
                        ? 'bg-green-100 border border-green-300 dark:bg-green-900/20 dark:border-green-500/40'
                        : 'bg-gray-50 dark:bg-gray-700/40 dark:border-transparent',
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
                                ? 'text-green-800 dark:text-green-200'
                                : 'text-gray-700 dark:text-gray-200',
                        ]"
                    >
                        {{ match.player_two.name }}
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <span
                        v-if="match.score2 !== undefined"
                        class="text-sm font-bold text-gray-900 dark:text-gray-100"
                        >{{ match.score2 }}</span
                    >
                    <span class="text-xs text-gray-500 dark:text-gray-400"
                        >({{ match.player_two.rating ?? "N/A" }})</span
                    >
                </div>
            </div>

            <!-- Placeholder for Player 2 -->
            <div
                v-else
                class="flex items-center justify-between p-2 rounded bg-gray-100 dark:bg-gray-700/40 mt-1"
            >
                <span class="text-sm text-gray-400 dark:text-gray-500"
                    >TBD</span
                >
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
