<template>
    <div class="h-full flex flex-col justify-center items-center">
        <div class="spinner-container flex justify-center items-center">
            <div
                class="spinner w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"
            ></div>
        </div>
        <p class="text-gray-500 mt-4">{{ props.text }}</p>
    </div>
</template>

<script setup lang="ts">
interface Props {
    text: string;
}

const props = defineProps<Props>();
</script>
