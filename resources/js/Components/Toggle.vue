<template>
    <div
        class="flex items-center rounded-full"
        :class="`${disabled ? 'opacity-50' : ''} ${backgroundColorClass}`"
    >
        <button
            :disabled="disabled ?? true"
            v-for="option in options"
            :key="option.value"
            class="px-3 py-1 text-sm font-medium rounded-full focus:outline-none transition-colors"
            :class="
                modelValue === option.value && !disabled
                    ? activeClasses
                    : inactiveClasses
            "
            @click="$emit('update:modelValue', option.value)"
        >
            {{ option.label }}
        </button>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

interface ToggleOption {
    label: string;
    value: string;
}

interface Props {
    modelValue: string;
    options: ToggleOption[];
    color?: "blue" | "green" | "indigo" | "purple" | "red" | "gray" | "orange";
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    color: "blue",
});

defineEmits(["update:modelValue"]);

const colorMap = {
    blue: {
        bg: "bg-blue-100 dark:bg-blue-900/30",
        active: "bg-blue-500 dark:bg-blue-400 text-white",
        inactive: "text-blue-800 dark:text-blue-300",
    },
    green: {
        bg: "bg-green-100 dark:bg-green-900/30",
        active: "bg-green-500 dark:bg-green-400 text-white",
        inactive: "text-green-800 dark:text-green-300",
    },
    indigo: {
        bg: "bg-indigo-100 dark:bg-indigo-900/30",
        active: "bg-indigo-500 dark:bg-indigo-400 text-white",
        inactive: "text-indigo-800 dark:text-indigo-300",
    },
    purple: {
        bg: "bg-purple-100 dark:bg-purple-900/30",
        active: "bg-purple-500 dark:bg-purple-400 text-white",
        inactive: "text-purple-800 dark:text-purple-300",
    },
    red: {
        bg: "bg-red-100 dark:bg-red-900/30",
        active: "bg-red-500 dark:bg-red-400 text-white",
        inactive: "text-red-800 dark:text-red-300",
    },
    gray: {
        bg: "bg-gray-100 dark:bg-gray-700",
        active: "bg-gray-500 dark:bg-gray-400 text-white",
        inactive: "text-gray-800 dark:text-gray-300",
    },
    orange: {
        bg: "bg-orange-100 dark:bg-orange-900/30",
        active: "bg-orange-500 dark:bg-orange-400 text-white",
        inactive: "text-orange-800 dark:text-orange-300",
    },
};

const backgroundColorClass = computed(() => colorMap[props.color].bg);
const activeClasses = computed(() => colorMap[props.color].active);
const inactiveClasses = computed(() => colorMap[props.color].inactive);
</script>
