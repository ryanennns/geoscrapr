<template>
    <tr
        :class="
            leaderboardRow.isPlaceholder
                ? 'opacity-50'
                : 'hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors cursor-pointer'
        "
        @click="
            leaderboardRow.isPlaceholder
                ? null
                : emit('player-click', leaderboardRow)
        "
        :data-testid="`row-${number}`"
    >
        <td class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitescpace-nowrap">
            <div
                class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100"
            >
                {{ number }}
            </div>
        </td>
        <td class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap">
            <div
                class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 truncate max-w-full"
            >
                {{
                    leaderboardRow.name.length > 17 && isMobile
                        ? leaderboardRow.name.slice(0, 14) + "..."
                        : leaderboardRow.name || "-"
                }}
            </div>
        </td>
        <td class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap">
            <div class="flex items-center">
                <div
                    v-for="countryCode in leaderboardRow.countryCodes"
                    class="flex"
                >
                    <Flag
                        :country-code="countryCode"
                        dimensions="120x90"
                        class="mr-1"
                        width="24"
                        height="18"
                        :class="{ 'sm:w-8 sm:h-6': true }"
                    />
                </div>
            </div>
        </td>
        <td class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap">
            <div
                class="text-xs sm:text-sm font-semibold text-indigo-700 dark:text-indigo-400"
            >
                {{ leaderboardRowToRating(leaderboardRow) }}
            </div>
        </td>
    </tr>
</template>
<script setup lang="ts">
import { type GameType, type LeaderboardRow } from "@/Types/core.ts";
import Flag from "@/Components/Flag.vue";
import { useBrowserUtils } from "@/Composables/useBrowserUtils.ts";

interface Props {
    leaderboardRow: LeaderboardRow;
    selectedGameType: GameType;
    number: number;
}

const props = defineProps<Props>();

const emit = defineEmits(["player-click"]);

const { isMobile } = useBrowserUtils();

const leaderboardRowToRating = (row: LeaderboardRow) => {
    if (row.isPlaceholder) {
        return "-";
    }

    switch (props.selectedGameType) {
        case "moving":
            return row.moving_rating?.toLocaleString() ?? "-";
        case "no_move":
            return row.no_move_rating?.toLocaleString() ?? "-";
        case "nmpz":
            return row.nmpz_rating?.toLocaleString() ?? "-";
        default:
            return row.rating?.toLocaleString() ?? "-";
    }
};
</script>
