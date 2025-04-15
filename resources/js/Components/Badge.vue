<template>
    <span class="text-sm font-medium px-3 py-1 rounded-full">
        {{ text }}
    </span>
</template>

<script setup>
defineProps({
    text: {
        type: String,
        required: true
    }
})
</script>
