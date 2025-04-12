<template>
    <li
        @mousedown.prevent="handlePlayerClick(props.player)"
        class="py-3 px-4 hover:bg-indigo-50 cursor-pointer transition-colors"
    >
        <div class="flex justify-between items-center">
            <span class="font-medium text-gray-800">
                {{ getFlagEmoji(props.player.country_code) }} {{ props.player.name }}
            </span>
            <span
                class="flex items-center justify-center bg-indigo-100 text-indigo-800 py-1 px-2 rounded-full text-sm font-semibold w-26 h-full">
                Rating: {{ props.player.rating }}
            </span>
        </div>
    </li>
</template>

<script setup>
import {usePlayerUtils} from "@composables/usePlayerUtils.js";

const {getFlagEmoji} = usePlayerUtils()

const props = defineProps({
    player: Object,
})

const emit = defineEmits(['playerClick']);

const handlePlayerClick = (player) => {
    emit('playerClick', player)
}
</script>

<style scoped>

</style>
