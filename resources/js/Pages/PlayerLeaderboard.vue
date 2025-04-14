<template>
    <div class="mt-10">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Rating Leaderboard</h2>
                <div class="relative">
                    <select
                        class="appearance-none bg-purple-100 text-purple-800 text-sm font-medium px-3 py-1 pr-8 rounded-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500"
                        @change="handleCountryFilterChange"
                    >
                        <option value="">🌎 All Countries</option>
                        <option v-for="country in availableCountries" :key="country.code" :value="country.code">
                            {{ getFlagEmoji(country.code) }} {{ country.name }}
                        </option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-purple-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 table-fixed">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="w-1/12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Rank
                        </th>
                        <th scope="col"
                            class="w-5/12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Player
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
                    <tr
                        v-for="(player, index) in displayRows"
                        :key="index"
                        :class="player.isPlaceholder ? 'opacity-50' : 'hover:bg-indigo-50 transition-colors cursor-pointer'"
                        @click="player.isPlaceholder ? null : handlePlayerClick(player)"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium">
                                {{ index + 1 }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ player.name || '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="mr-2 text-2xl">
                                    {{ player.country_code ? getFlagEmoji(player.country_code) : '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-indigo-700">
                                {{ player.rating?.toLocaleString() ?? "-" }}
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script setup>
import {ref, computed} from "vue";
import {usePlayerUtils} from "@composables/usePlayerUtils.js";

const emit = defineEmits(['playerClick', 'countryFilterChange'])
const props = defineProps({
    players: Array,
})

const {getFlagEmoji, countryMap} = usePlayerUtils()

const leaderboardData = ref(props.players || []);
const selectedCountry = ref('');

const handlePlayerClick = (player) => emit('playerClick', {player})

const handleCountryFilterChange = async (event) => {
    selectedCountry.value = event.target.value;

    if (event.target.value === '') {
        leaderboardData.value = props.players;

        return;
    }

    const response = await fetch(`/players?country=${event.target.value}`);

    if (!response.ok) {
        throw new Error()
    }

    leaderboardData.value = await response.json() ?? [];
}

const availableCountries = computed(() => {
    return Object.keys(countryMap).map(c => ({
        code: c,
        name: countryMap[c],
    })).sort((a,b) => a.name.localeCompare(b.name))
});

const displayRows = computed(() => {
    const rows = [...leaderboardData.value];
    const placeholderCount = Math.max(0, 10 - rows.length);

    for (let i = 0; i < placeholderCount; i++) {
        rows.push({
            id: `placeholder-${i}`,
            name: '-',
            country_code: null,
            rating: null,
            isPlaceholder: true
        });
    }

    return rows;
});
</script>
