<template>
    <div
        class="bg-blue-50 rounded-lg px-3 py-1 flex items-center"
    >
        <span class="text-sm font-medium">{{ props.label }}</span>
        <span class="text-sm font-bold ml-1">
            {{ props.text }}
        </span>
    </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';

interface Props {
    label: string;
    text: string;
}

const props = defineProps<Props>();
</script>
