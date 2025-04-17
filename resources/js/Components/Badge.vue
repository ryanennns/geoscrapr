<template>
    <span class="text-sm font-medium px-3 py-1 rounded-full">
        {{ props.text }}
    </span>
</template>

<script setup lang="ts">
interface Props {
    text: string;
}

const props = defineProps<Props>();
</script>
