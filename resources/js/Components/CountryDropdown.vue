<template>
    <select
        class="appearance-none bg-purple-100 dark:bg-gray-700 text-purple-800 dark:text-purple-300 text-sm font-medium px-3 py-1 pr-8 rounded-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400"
        @change="handleCountryFilterChange"
        :disabled="props.disabled"
        :class="{
            'opacity-50 cursor-not-allowed': props.disabled,
        }"
        data-testid="country-dropdown"
    >
        <option value="">🌎 All Countries</option>
        <option
            v-for="country in availableCountries"
            :key="country.code"
            :value="country.code"
        >
            {{ getFlagEmoji(country.code) }} {{ country.name }}
        </option>
    </select>
    <div
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-purple-800 dark:text-purple-300"
    >
        <svg
            class="h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
            />
        </svg>
    </div>
</template>

<script setup lang="ts">
import {
    CountryCode,
    countryMap,
    usePlayerUtils,
} from "@/Composables/usePlayerUtils.js";
import { computed, onMounted, ref } from "vue";
import { useApiClient } from "@/Composables/useApiClient.ts";

interface Props {
    modelValue: string;
    disabled: boolean;
}

const props = defineProps<Props>();

const { getFlagEmoji } = usePlayerUtils();
const { getAvailableCountries } = useApiClient();

const apiCountries = ref<CountryCode[]>([]);

interface Country {
    code: string;
    name: string;
}

const countryMapMethod = (c: string): Country => ({
    code: c as CountryCode,
    name: countryMap[c as CountryCode],
});
const countrySortMethod = (a: Country, b: Country) =>
    a.name?.localeCompare(b.name);
const availableCountries = computed(() => {
    return apiCountries.value.length < 1
        ? Object.keys(countryMap).map(countryMapMethod).sort(countrySortMethod)
        : apiCountries.value.map(countryMapMethod).sort(countrySortMethod);
});

const emit = defineEmits(["update:modelValue"]);

const handleCountryFilterChange = (event: Event) => {
    emit("update:modelValue", (event.target as HTMLSelectElement)?.value);
};

onMounted(async () => {
    const availableCountries = await getAvailableCountries();

    if (availableCountries.error) {
        return;
    }

    apiCountries.value = availableCountries?.data ?? [];
});
</script>
