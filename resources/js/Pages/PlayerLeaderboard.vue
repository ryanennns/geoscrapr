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

                    <div class="relative">
                        <select
                            class="appearance-none bg-green-100 text-green-800 text-sm font-medium px-3 py-1 pr-8 rounded-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-green-500"
                            @change="handleSortOrderChange"
                        >
                            <option value="desc">🔽 Descending</option>
                            <option value="asc">🔼 Ascending</option>
                        </select>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-green-800"
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
                            {{ isSolo ? "Player" : "Team" }}
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
                                <div v-if="player.country_code">
                                    <img
                                        :src="`https://flagcdn.com/32x24/${player.country_code}.png`"
                                        :alt="player.country_code"
                                        :title="countryMap[player.country_code]"
                                    >
                                </div>
                                <div v-else-if="player.player_a?.country_code && player.player_b?.country_code" class="flex">
                                    <img
                                        class="px-1"
                                        :src="`https://flagcdn.com/32x24/${player.player_a.country_code}.png`"
                                        :alt="player.country_code"
                                        :title="countryMap[player.country_code]"
                                    >
                                    <img
                                        class="px-1"
                                        :src="`https://flagcdn.com/32x24/${player.player_b.country_code}.png`"
                                        :alt="player.country_code"
                                        :title="countryMap[player.country_code]"
                                    >
                                </div>
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
import {ref, computed, watch, onMounted} from "vue";
import {countryMap, usePlayerUtils} from "@composables/usePlayerUtils.js";
import CountryDropdown from "../Components/CountryDropdown.vue";

const emit = defineEmits(['playerClick', 'countryFilterChange'])
const props = defineProps({
    players: Array,
});

const {getFlagEmoji} = usePlayerUtils();

const dataCache = ref({
    asc: {
        solo: {
            all: null,
            byCountry: {}
        },
        team: {
            all: null,
        }
    },
    desc: {
        solo: {
            all: null,
            byCountry: {}
        },
        team: {
            all: null,
        }
    }
});

const leaderboardData = ref(props.players || []);
const selectedCountry = ref('');
const selectedMode = ref("solo");
const isLoading = ref(false);

onMounted(() => {
    if (props.players && props.players.length > 0) {
        dataCache.value[selectedOrder.value].solo.all = props.players;
    }
});

const isSolo = computed(() => {
    return selectedMode.value === "solo";
});

const handleModeChange = (event) => {
    selectedMode.value = event.target.value;
    updateLeaderboard();
};

const handleCountryFilterChange = (event) => {
    selectedCountry.value = event.country;
    updateLeaderboard();
};

const updateLeaderboard = async () => {
    const mode = selectedMode.value;
    const country = selectedCountry.value;

    if (country === "" && mode === "solo" && dataCache.value[selectedOrder.value].solo.all) {
        leaderboardData.value = dataCache.value[selectedOrder.value].solo.all;
        return;
    }

    if (mode === "team" && dataCache.value[selectedOrder.value].team.all) {
        leaderboardData.value = dataCache.value[selectedOrder.value].team.all;
        return;
    }

    const cacheKey = country || 'all';
    if (dataCache.value[selectedOrder.value][mode][cacheKey] !== undefined && dataCache.value[selectedOrder.value][mode][cacheKey] !== null) {
        leaderboardData.value = dataCache.value[selectedOrder.value][mode][cacheKey];
        return;
    }

    try {
        isLoading.value = true;
        const url = mode === "solo" ? "players" : "teams";

        const params = new URLSearchParams();

        if (country) {
            params.append("country", country);
        }

        if (selectedOrder.value) {
            params.append("order", selectedOrder.value);
        }

        const response = await fetch(`/${url}?${params.toString()}`);

        if (!response.ok) {
            throw new Error(`Failed to fetch ${mode} data for ${country || 'all countries'}`);
        }

        const json = await response.json();

        dataCache.value[selectedOrder.value][mode][cacheKey] = json;

        leaderboardData.value = json || [];
    } catch (error) {
        console.error("Error fetching leaderboard data:", error);
        leaderboardData.value = [];
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.players, (newPlayers) => {
    if (newPlayers && newPlayers.length > 0) {
        dataCache.value[selectedOrder.value].solo.all = newPlayers;

        if (selectedMode.value === "solo" && !selectedCountry.value) {
            leaderboardData.value = newPlayers;
        }
    }
}, {deep: true});

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
        if (r.id && r.id.includes('placeholder')) {
            return {...r, flag: '🏴'}
        }

        if (r.country_code) {
            return {...r, flag: getFlagEmoji(r.country_code)}
        }

        return {...r, flag: `${getFlagEmoji(r.player_a.country_code)}/${getFlagEmoji(r.player_b.country_code)}`}
    });
});

const handlePlayerClick = (player) => {
    if (player.player_a || player.player_b) {
        return;
    }

    emit("playerClick", {player})
};

const selectedOrder = ref("desc");
const handleSortOrderChange = (event) => {
    selectedOrder.value = event.target.value;
    updateLeaderboard();
};

</script>
