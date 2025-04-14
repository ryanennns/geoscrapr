<template>
    <div class="mt-10">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Rating Leaderboard</h2>
                <div class="flex space-x-4">
                    <div class="relative">
                        <CountryDropdown
                            @change="handleCountryFilterChange"
                            :disabled="!isSolo"
                        />
                    </div>

                    <div class="relative">
                        <select
                            class="appearance-none bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 pr-8 rounded-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="handleModeChange"
                        >
                            <option value="solo">Solo</option>
                            <option value="team">Team</option>
                        </select>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-blue-800"
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
                                ></path>
                            </svg>
                        </div>
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
                                    {{ player.flag }}
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
import {ref, computed, watch} from "vue";
import {usePlayerUtils} from "@composables/usePlayerUtils.js";
import CountryDropdown from "../Components/CountryDropdown.vue";

const emit = defineEmits(['playerClick', 'countryFilterChange'])
const props = defineProps({
    players: Array,
})

const {getFlagEmoji} = usePlayerUtils();

const leaderboardData = ref(props.players || []);
const selectedCountry = ref('');
const selectedMode = ref("solo");

const isSolo = computed(() => {
    return selectedMode.value === "solo";
});

const handleModeChange = async (event) => {
    selectedMode.value = event.target.value;
};

const handleCountryFilterChange = async (event) => {
    selectedCountry.value = event.country;
};

watch([selectedCountry, selectedMode], async () => {
    const url = selectedMode.value === "solo" ? "players" : "teams";

    if (selectedCountry.value === "" && selectedMode.value === "solo") {
        leaderboardData.value = props.players;

        return;
    }

    const response = await fetch(`/${url}?country=${selectedCountry.value}`);

    if (!response.ok) {
        throw new Error();
    }

    const json = await response.json();

    leaderboardData.value = json ?? [];
});

const displayRows = computed(() => {
    const rows = [...leaderboardData.value];
    const placeholderCount = Math.max(0, 10 - rows.length);

    for (let i = 0; i < placeholderCount; i++) {
        rows.push({
            id: `placeholder-${i}`,
            name: "-",
            country_code: null,
            rating: null,
            isPlaceholder: true,
        });
    }

    return rows.map((r) => {
        if (r.id.includes('placeholder')) {
            return {...r, flag: '🏴'}
        }

        if (r.country_code) {
            return {...r, flag: getFlagEmoji(r.country_code)}
        }

        return {...r, flag: `${getFlagEmoji(r.player_a.country_code)}/${getFlagEmoji(r.player_b.country_code)}`}
    });
});

const handlePlayerClick = (player) => emit("playerClick", {player});
</script>
