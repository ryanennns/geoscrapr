<template>
    <div class="relative group">
        <div
            class="rounded-lg px-3 py-1 flex items-center cursor-default"
            :class="variantClasses"
        >
            <span class="text-sm font-bold">{{ props.text }}</span>
        </div>
        <div
            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 dark:bg-gray-600 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50"
        >
            {{ props.tooltip }}
            <div
                class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900 dark:border-t-gray-600"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

interface Props {
    text: string;
    tooltip: string;
    variant: "moving" | "no-moving" | "nmpz";
}

const props = defineProps<Props>();

const variantClasses = computed(() => {
    switch (props.variant) {
        case "moving":
            return "bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300";
        case "no-moving":
            return "bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300";
        case "nmpz":
            return "bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300";
    }
});
</script>
