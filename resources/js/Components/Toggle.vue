<template>
    <div class="flex items-center rounded-full" :class="backgroundColorClass">
        <button
            v-for="option in options"
            :key="option.value"
            class="px-3 py-1 text-sm font-medium rounded-full focus:outline-none transition-colors"
            :class="
                modelValue === option.value ? activeClasses : inactiveClasses
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
}

const props = withDefaults(defineProps<Props>(), {
    color: "blue",
});

defineEmits(["update:modelValue"]);

const colorMap = {
    blue: {
        bg: "bg-blue-100",
        active: "bg-blue-500 text-white",
        inactive: "text-blue-800",
    },
    green: {
        bg: "bg-green-100",
        active: "bg-green-500 text-white",
        inactive: "text-green-800",
    },
    indigo: {
        bg: "bg-indigo-100",
        active: "bg-indigo-500 text-white",
        inactive: "text-indigo-800",
    },
    purple: {
        bg: "bg-purple-100",
        active: "bg-purple-500 text-white",
        inactive: "text-purple-800",
    },
    red: {
        bg: "bg-red-100",
        active: "bg-red-500 text-white",
        inactive: "text-red-800",
    },
    gray: {
        bg: "bg-gray-100",
        active: "bg-gray-500 text-white",
        inactive: "text-gray-800",
    },
    orange: {
        bg: "bg-orange-100",
        active: "bg-orange-500 text-white",
        inactive: "text-orange-800",
    }
};

const backgroundColorClass = computed(() => colorMap[props.color].bg);
const activeClasses = computed(() => colorMap[props.color].active);
const inactiveClasses = computed(() => colorMap[props.color].inactive);
</script>
