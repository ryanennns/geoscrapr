<template>
    <table class="min-w-full divide-y divide-gray-200 table-fixed animate-pulse">
        <thead class="bg-gray-50">
        <tr>
            <th scope="col"
                class="w-1/12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Rank
            </th>
            <th
                scope="col"
                class="w-5/12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
                {{ isSolo ? 'Player' : 'Team' }}
            </th>
            <th scope="col"
                class="w-3/12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Country
            </th>
            <th scope="col"
                class="w-3/12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Rating
            </th>
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="i in 10" :key="i">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="h-4 bg-gray-200 rounded w-6"></div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="h-4 bg-gray-200 rounded w-1/6"></div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="props.isSolo">
                    <div class="h-6 w-9 bg-gray-200 rounded"/>
                </div>
                <div v-else class="flex">
                    <div class="h-6 w-9 bg-gray-200 rounded mx-1"/>
                    <div class="h-6 w-9 bg-gray-200 rounded mx-1"/>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="h-3.5 bg-gray-300 rounded w-1/16"></div>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script setup lang="ts">
interface Props {
    isSolo: boolean
}

const props = defineProps<Props>()
</script>
