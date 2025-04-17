<template>
    <img
        :src="getFlagImg(props.countryCode, props.dimensions)"
        :alt="countryMap[props.countryCode]"
        :title="countryMap[props.countryCode]"
    >
</template>

<script setup lang="ts">
import {countryMap, usePlayerUtils} from "@/composables/usePlayerUtils.js";

interface Props {
    countryCode: string
    dimensions: string
}

const props = defineProps<Props>()

const {getFlagImg} = usePlayerUtils();
</script>
