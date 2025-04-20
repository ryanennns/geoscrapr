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
                    Rating
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="i in 10" :key="i">
                <td class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap">
                    <div class="h-[18px] bg-gray-200 rounded w-3"></div>
                </td>
                <td class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap">
                    <div
                        class="h-[18px] bg-gray-200 rounded max-w-full w-2/3"
                    ></div>
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
                    <div class="h-[14px] bg-gray-300 rounded w-12"></div>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script setup lang="ts">
interface Props {
    isSolo: boolean;
}

const props = defineProps<Props>();
</script>
