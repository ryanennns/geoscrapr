<template>
    <table
        class="min-w-full divide-y divide-gray-200 table-fixed animate-pulse"
    >
        <thead class="bg-gray-50">
            <tr>
                <th
                    scope="col"
                    class="px-2 sm:px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    style="width: 40px"
                >
                    #
                </th>
                <th
                    scope="col"
                    class="w-5/12 px-2 sm:px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                    {{ isSolo ? "Player" : "Team" }}
                </th>
                <th
                    scope="col"
                    class="w-3/12 px-2 sm:px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                    <span class="hidden sm:inline">Country</span>
                    <span class="sm:hidden">Flag</span>
                </th>
                <th
                    scope="col"
                    class="w-3/12 px-2 sm:px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                    {{ ratingHeader }}
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(w, i) in widths" :key="i">
                <td class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap">
                    <div class="h-[18px] bg-gray-200 rounded w-5"></div>
                </td>
                <td>
                    <div
                        :style="{ width: w }"
                        class="h-[18px] bg-gray-200 rounded max-w-full"
                    />
                </td>
                <td class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap">
                    <div v-if="props.isSolo">
                        <div
                            class="h-[18px] w-[24px] sm:h-[24px] sm:w-[32px] bg-gray-200 rounded"
                        />
                    </div>
                    <div v-else class="flex">
                        <div
                            class="h-[18px] w-[24px] sm:h-[24px] sm:w-[32px] bg-gray-200 rounded mr-1"
                        />
                        <div
                            class="h-[18px] w-[24px] sm:h-[24px] sm:w-[32px] bg-gray-200 rounded ml-1"
                        />
                    </div>
                </td>
                <td class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap">
                    <div class="h-[15px] bg-gray-300 rounded w-9"></div>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";

interface Props {
    show: boolean;
    isSolo: boolean;
    ratingHeader: string;
}

const props = defineProps<Props>();

const getWidths = () =>
    Array.from({ length: 10 }, () => `${Math.floor(Math.random() * 25) + 10}%`);

const widths = ref<string[]>(getWidths());

watch(props, () => {
    widths.value = getWidths();
});
</script>
