<template>
    <img
        :src="getFlagImg(props.countryCode, props.dimensions)"
        :alt="countryMap[props.countryCode]"
        :title="countryMap[props.countryCode]"
    >
</template>

<script setup>
import {countryMap, usePlayerUtils} from "@composables/usePlayerUtils.js";

const props = defineProps({
    countryCode: String,
    dimensions: String,
})

const {getFlagImg} = usePlayerUtils();
</script>
