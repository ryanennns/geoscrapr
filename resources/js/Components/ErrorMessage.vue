<template>
    <div class="h-full flex flex-col justify-center items-center">
        <svg
            class="h-16 w-16 text-gray-400 dark:text-gray-500 mb-4"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
        </svg>
        <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">
            {{ props.heading }}
        </p>
        <p class="text-gray-500 dark:text-gray-400 mt-2">
            {{ props.subHeading }}
        </p>
    </div>
</template>
<script setup lang="ts">
interface Props {
    heading: string;
    subHeading: string;
}

const props = defineProps<Props>();
</script>
