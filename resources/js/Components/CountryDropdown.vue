<template>
    <div ref="root" class="relative">
        <button
            type="button"
            ref="triggerButton"
            class="flex items-center justify-between gap-3 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm font-medium px-3 py-2 pr-9 rounded-full border border-gray-200 dark:border-gray-600 shadow-sm transition transition-[width] duration-200 ease-out focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="props.disabled"
            :aria-expanded="isOpen"
            aria-haspopup="listbox"
            :aria-controls="listboxId"
            data-testid="country-dropdown"
            :style="buttonStyle"
            @click="toggle"
            @keydown.down.prevent="openAndFocus"
            @keydown.enter.prevent="openAndFocus"
            @keydown.space.prevent="openAndFocus"
        >
            <span ref="labelSpan" class="flex items-center gap-2 truncate">
                {{ selectedLabel }}
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

        <Transition
            enter-active-class="transition opacity duration-150 ease-out delay-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition opacity duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isDropdownVisible"
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
                                'bg-gray-100 dark:bg-gray-700':
                                    props.modelValue.length === 0,
                                'hover:bg-emerald-100 dark:hover:bg-emerald-900/40':
                                    props.modelValue.length === 0,
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
                    <li
                        v-for="country in filteredCountries"
                        :key="country.code"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                            :class="{
                                'bg-gray-100 dark:bg-gray-700':
                                    props.modelValue.includes(country.code),
                                'hover:bg-emerald-100 dark:hover:bg-emerald-900/40':
                                    props.modelValue.includes(country.code),
                            }"
                            @click="selectCountry(country.code)"
                        >
                            {{ getFlagEmoji(country.code) }} {{ country.name }}
                        </button>
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import {
    CountryCode,
    countryMap,
    usePlayerUtils,
} from "@/Composables/usePlayerUtils.js";
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { useApiClient } from "@/Composables/useApiClient.ts";

interface Props {
    modelValue: string[];
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
const triggerButton = ref<HTMLButtonElement | null>(null);
const labelSpan = ref<HTMLSpanElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);
const searchQuery = ref("");
const isOpen = ref(false);
const isDropdownVisible = ref(false);
const listboxId = "country-listbox";
const buttonWidth = ref<number | null>(null);
const dropdownOpenMinWidth = 288;
const closeTimeoutId = ref<number | null>(null);

const selectedCountries = computed(() => {
    if (!props.modelValue.length) return [];
    const selected = new Set(props.modelValue);
    return availableCountries.value.filter((country) =>
        selected.has(country.code),
    );
});

const selectedLabel = computed(() => {
    if (selectedCountries.value.length === 0) return "🌎";
    return selectedCountries.value
        .map((country) => getFlagEmoji(country.code))
        .join(" ");
});

const filteredCountries = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    const filtered = !query
        ? availableCountries.value
        : availableCountries.value.filter((country) => {
              return (
                  country.name.toLowerCase().includes(query) ||
                  country.code.toLowerCase().includes(query)
              );
          });

    if (props.modelValue.length === 0) {
        return filtered;
    }

    const selected = new Set(props.modelValue);
    return [...filtered].sort((a, b) => {
        const aSelected = selected.has(a.code);
        const bSelected = selected.has(b.code);
        if (aSelected === bSelected) return 0;
        return aSelected ? -1 : 1;
    });
});

const openAndFocus = async () => {
    if (props.disabled) return;
    isOpen.value = true;
    isDropdownVisible.value = true;
    if (closeTimeoutId.value !== null) {
        window.clearTimeout(closeTimeoutId.value);
        closeTimeoutId.value = null;
    }
    await nextTick();
    searchInput.value?.focus();
};

const close = () => {
    if (!isOpen.value && !isDropdownVisible.value) return;
    isDropdownVisible.value = false;
    searchQuery.value = "";
    if (closeTimeoutId.value !== null) {
        window.clearTimeout(closeTimeoutId.value);
    }
    closeTimeoutId.value = window.setTimeout(() => {
        isOpen.value = false;
        closeTimeoutId.value = null;
    }, 100);
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
    if (!code) {
        emit("update:modelValue", []);
        close();
        return;
    }

    const selected = new Set(props.modelValue);
    if (selected.has(code)) {
        selected.delete(code);
    } else {
        selected.add(code);
    }
    emit("update:modelValue", Array.from(selected));
};

const updateButtonWidth = () => {
    if (!triggerButton.value || !labelSpan.value) return;
    const styles = window.getComputedStyle(triggerButton.value);
    const paddingLeft = parseFloat(styles.paddingLeft) || 0;
    const paddingRight = parseFloat(styles.paddingRight) || 0;
    const borderLeft = parseFloat(styles.borderLeftWidth) || 0;
    const borderRight = parseFloat(styles.borderRightWidth) || 0;
    const labelWidth = labelSpan.value.scrollWidth;
    const baseWidth = Math.ceil(
        labelWidth + paddingLeft + paddingRight + borderLeft + borderRight,
    );
    const targetWidth = isOpen.value
        ? Math.max(baseWidth, dropdownOpenMinWidth)
        : baseWidth;
    buttonWidth.value = targetWidth;
};

const buttonStyle = computed(() => {
    if (buttonWidth.value == null) return undefined;
    return { width: `${buttonWidth.value}px` };
});

onMounted(async () => {
    const availableCountries = await getAvailableCountries();

    if (availableCountries.error) {
        return;
    }

    apiCountries.value = availableCountries?.data ?? [];
});

onMounted(() => {
    updateButtonWidth();
    window.addEventListener("resize", updateButtonWidth);
});

onUnmounted(() => {
    window.removeEventListener("resize", updateButtonWidth);
    if (closeTimeoutId.value !== null) {
        window.clearTimeout(closeTimeoutId.value);
    }
});

watch([selectedLabel, isOpen], async () => {
    await nextTick();
    updateButtonWidth();
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
