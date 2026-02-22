<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="flex items-center justify-between gap-3 min-w-[220px] bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm font-medium px-3 py-2 pr-9 rounded-full border border-gray-200 dark:border-gray-600 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="props.disabled"
            :aria-expanded="isOpen"
            aria-haspopup="listbox"
            :aria-controls="listboxId"
            data-testid="country-dropdown"
            @click="toggle"
            @keydown.down.prevent="openAndFocus"
            @keydown.enter.prevent="openAndFocus"
            @keydown.space.prevent="openAndFocus"
        >
            <span class="flex items-center gap-2 truncate">
                <span v-if="selectedCountry">
                    {{ getFlagEmoji(selectedCountry.code) }}
                    {{ selectedCountry.name }}
                </span>
                <span v-else>🌎 All Countries</span>
            </span>
            <span
                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400"
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
            </span>
        </button>

        <div
            v-if="isOpen"
            class="absolute z-20 mt-2 w-72 max-w-[calc(100vw-2rem)] rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg"
        >
            <div class="p-2">
                <input
                    ref="searchInput"
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search country…"
                    class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-sm text-gray-800 dark:text-gray-100 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    @keydown.escape.prevent="close"
                />
            </div>
            <ul
                :id="listboxId"
                role="listbox"
                class="max-h-64 overflow-y-auto pb-2"
            >
                <li>
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                        :class="{
                            'bg-gray-100 dark:bg-gray-700': !props.modelValue,
                        }"
                        @click="selectCountry('')"
                    >
                        🌎 All Countries
                    </button>
                </li>
                <li v-if="filteredCountries.length < 1">
                    <div
                        class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        No countries match that search.
                    </div>
                </li>
                <li v-for="country in filteredCountries" :key="country.code">
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                        :class="{
                            'bg-gray-100 dark:bg-gray-700':
                                country.code === props.modelValue,
                        }"
                        @click="selectCountry(country.code)"
                    >
                        {{ getFlagEmoji(country.code) }} {{ country.name }}
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import {
    CountryCode,
    countryMap,
    usePlayerUtils,
} from "@/Composables/usePlayerUtils.js";
import { computed, nextTick, onMounted, onUnmounted, ref } from "vue";
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
    name: countryMap[c as CountryCode] ?? c.toUpperCase(),
});
const countrySortMethod = (a: Country, b: Country) =>
    a.name?.localeCompare(b.name);
const availableCountries = computed(() => {
    return apiCountries.value.length < 1
        ? Object.keys(countryMap).map(countryMapMethod).sort(countrySortMethod)
        : apiCountries.value.map(countryMapMethod).sort(countrySortMethod);
});

const emit = defineEmits(["update:modelValue"]);

const root = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);
const searchQuery = ref("");
const isOpen = ref(false);
const listboxId = "country-listbox";

const selectedCountry = computed(() =>
    availableCountries.value.find(
        (country) => country.code === props.modelValue,
    ),
);

const filteredCountries = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    if (!query) return availableCountries.value;
    return availableCountries.value.filter((country) => {
        return (
            country.name.toLowerCase().includes(query) ||
            country.code.toLowerCase().includes(query)
        );
    });
});

const openAndFocus = async () => {
    if (props.disabled) return;
    isOpen.value = true;
    await nextTick();
    searchInput.value?.focus();
};

const close = () => {
    isOpen.value = false;
    searchQuery.value = "";
};

const toggle = () => {
    if (props.disabled) return;
    if (isOpen.value) {
        close();
    } else {
        openAndFocus();
    }
};

const selectCountry = (code: string) => {
    emit("update:modelValue", code);
    close();
};

onMounted(async () => {
    const availableCountries = await getAvailableCountries();

    if (availableCountries.error) {
        return;
    }

    apiCountries.value = availableCountries?.data ?? [];
});

const onDocumentClick = (event: MouseEvent) => {
    if (!isOpen.value) return;
    if (!root.value?.contains(event.target as Node)) {
        close();
    }
};

onMounted(() => {
    document.addEventListener("click", onDocumentClick);
});

onUnmounted(() => {
    document.removeEventListener("click", onDocumentClick);
});
</script>
