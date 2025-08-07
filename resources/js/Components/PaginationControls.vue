<template>
    <div class="mt-4 border-t border-gray-300 pt-4">
        <div class="flex items-center justify-end gap-4">
            <button
                type="button"
                class="inline-flex items-center rounded-lg border px-3 py-2 text-sm disabled:opacity-50 outline-none focus:outline-none"
                aria-label="Previous page"
                @click="decrement"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="h-5 w-5"
                >
                    <path
                        fill-rule="evenodd"
                        d="M15.53 4.47a.75.75 0 010 1.06L9.06 12l6.47 6.47a.75.75 0 01-1.06 1.06l-7-7a.75.75 0 010-1.06l7-7a.75.75 0 011.06 0z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>

            <div class="text-sm font-medium select-none">
                Page {{ props.modelValue }}
            </div>

            <button
                type="button"
                class="inline-flex items-center rounded-lg border px-3 py-2 text-sm disabled:opacity-50 outline-none focus:outline-none"
                aria-label="Next page"
                @click="increment"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="h-5 w-5"
                >
                    <path
                        fill-rule="evenodd"
                        d="M8.47 19.53a.75.75 0 010-1.06L14.94 12 8.47 5.53a.75.75 0 111.06-1.06l7 7a.75.75 0 010 1.06l-7 7a.75.75 0 01-1.06 0z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { watch } from "vue";

const props = defineProps<{
    modelValue: number;
}>();
const emit = defineEmits(["update:modelValue", "page-changed"]);

const increment = () => emit("update:modelValue", props.modelValue + 1);
const decrement = () =>
    emit("update:modelValue", Math.max(props.modelValue - 1, 1));

watch(
    () => props.modelValue,
    (newPage) => emit("page-changed", newPage),
);
</script>
