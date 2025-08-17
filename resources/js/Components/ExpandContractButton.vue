<template>
    <button @click="emit('toggle')" class="text-gray-500 hover:text-gray-700">
        <svg
            class="h-6 w-6"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="props.expanded ? 'M9 9H4 M9 9V4' : 'M4 9V4h5'"
            />
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="props.expanded ? 'M15 15H20 M15 15V20' : 'M20 15v5h-5'"
            />
        </svg>
    </button>
</template>

<script setup lang="ts">
const emit = defineEmits(["toggle"]);
const props = defineProps<{ expanded: boolean }>();
</script>
