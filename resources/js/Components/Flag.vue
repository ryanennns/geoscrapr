<template>
    <img
        :src="getFlagImg(props.countryCode, props.dimensions)"
        :alt="countryMap[props.countryCode]"
        :title="countryMap[props.countryCode]"
        @error="
            (e) =>
                ((e.target as HTMLImageElement).src = getFlagImg(
                    'white',
                    props.dimensions,
                ))
        "
    />
</template>

<script setup lang="ts">
import {
    CountryCode,
    countryMap,
    usePlayerUtils,
} from "@/Composables/usePlayerUtils.js";

interface Props {
    countryCode: CountryCode;
    dimensions?: string;
}

const props = defineProps<Props>();

const { getFlagImg } = usePlayerUtils();
</script>
