<template>
    <select
        class="appearance-none bg-purple-100 text-purple-800 text-sm font-medium px-3 py-1 pr-8 rounded-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500"
        @change="handleCountryFilterChange"
        :disabled="props.disabled"
        :class="{
                'opacity-50 cursor-not-allowed': props.disabled
            }"
    >
        <option value="">🌎 All Countries</option>
        <option v-for="country in availableCountries" :key="country.code" :value="country.code">
            {{ getFlagEmoji(country.code) }} {{ country.name }}
        </option>
    </select>
    <div
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-purple-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
             xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
</template>

<script setup>
import {countryMap, usePlayerUtils} from "@composables/usePlayerUtils.js";
import {computed, onMounted, ref} from "vue";

const {getFlagEmoji} = usePlayerUtils()

const props = defineProps({disabled: Boolean})

const apiCountries = ref([]);
const countryMapMethod = c => ({ code: c, name: countryMap[c] })
const countrySortMethod = (a, b) => a.name?.localeCompare(b.name);
const availableCountries = computed(() => {
    return apiCountries.value.length < 1 ?
        Object.keys(countryMap)
            .map(countryMapMethod)
            .sort(countrySortMethod) :
        apiCountries.value
            .map(countryMapMethod)
            .sort(countrySortMethod);
});

const emits = defineEmits(['change'])

const handleCountryFilterChange = (event) => {
    emits('change', {country: event.target.value})
}

onMounted(async () => {
    const response = await fetch('countries');

    if (!response.ok) {
        throw new Error(response.status);

        return;
    }

    apiCountries.value = await response.json();
})
</script>
