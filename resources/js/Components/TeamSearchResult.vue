<template>
    <li
        @mousedown.prevent="handlePlayerClick(props.team)"
        class="py-3 px-4 hover:bg-indigo-50 cursor-pointer transition-colors"
    >
        <div class="flex justify-between items-center">
            <span class="font-medium text-gray-800">
                {{ getFlagEmoji(props.team.player_a.country_code) }} {{ getFlagEmoji(props.team.player_b.country_code) }}
                {{ props.team.name }}
            </span>
            <span
                class="flex items-center justify-center bg-indigo-100 text-indigo-800 py-1 px-2 rounded-full text-sm font-semibold w-26 h-full"
            >
                Rating: {{ props.team.rating }}
            </span>
        </div>
    </li>
</template>

<script setup lang="ts">
import {usePlayerUtils} from "@/composables/usePlayerUtils.js";
import type {Team} from "@/Types/core.ts";

interface Props {
    team: Team;
}

const props = defineProps<Props>();

const {getFlagEmoji} = usePlayerUtils();

const emit = defineEmits(["teamClick"]);

const handlePlayerClick = (team: Team) => {
    emit("teamClick", team);
};
</script>

<style scoped></style>
