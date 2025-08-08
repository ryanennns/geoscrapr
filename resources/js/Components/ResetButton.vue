<template>
    <div class="flex items-center rounded-full">
        <button
            class="px-3 py-1 text-sm font-medium rounded-full focus:outline-none transition-colors bg-gray-200 hover:cursor-pointer"
            @click="$emit('click')"
        >
            Clear
        </button>
    </div>
</template>

<script setup lang="ts">
defineEmits(["click"]);
</script>
