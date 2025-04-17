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
                <LeaderboardLoadingSkeleton
                    v-show="loading"
                    :is-solo="isSolo"
                />
                <table v-show="!loading" class="min-w-full divide-y divide-gray-200 table-fixed">
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
                        v-for="(leaderboardRow, index) in leaderboardRows"
                        :key="index"
                        :class="leaderboardRow.isPlaceholder ? 'opacity-50' : 'hover:bg-indigo-50 transition-colors cursor-pointer'"
                        @click="leaderboardRow.isPlaceholder ? null : handlePlayerClick(leaderboardRow)"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium">
                                {{ index + 1 }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ leaderboardRow.name || '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div v-for="countryCode in leaderboardRow.countryCodes" class="flex">
                                    <Flag
                                        :country-code="countryCode"
                                        :dimensions="FLAG_IMG_SIZE"
                                        class="mr-1"
                                    />
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-indigo-700">
                                {{ leaderboardRow.rating?.toLocaleString() ?? "-" }}
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import {computed, onMounted, ref, watch} from "vue";
import CountryDropdown from "@/Components/CountryDropdown.vue";
import LeaderboardLoadingSkeleton from "@/Components/LeaderboardLoadingSkeleton.vue";
import Flag from "@/Components/Flag.vue";
import {isTeam, type Player, type Team, type LeaderboardRow, type Rateable} from "@/Types/core.ts";

const sortOrders = ['asc', 'desc'] as const;
type SortOrder = (typeof sortOrders)[number]
const isSortOrder = (a: any): a is SortOrder => sortOrders.includes(a);

interface Props {
    playersOrTeams: Rateable[],
}

const props = defineProps<Props>()

interface SubCache {
    [key: string]: Rateable[]
}

type PlayerTeamCache = Record<SortOrder, Record<Gamemode, SubCache>>

const FLAG_IMG_SIZE = '32x24';

const emit = defineEmits(['playerClick', 'countryFilterChange'])

const dataCache = ref<PlayerTeamCache>({
    'asc': {
        'solo': {
            all: [],
        },
        'team': {
            all: [],
        }
    },
    'desc': {
        'solo': {
            all: [],
        },
        'team': {
            all: [],
        }
    }
});

const rateables = ref<Rateable[]>(props.playersOrTeams);
const loading = ref(false);

type Gamemode = 'solo' | 'team'
const selectedMode = ref<Gamemode>("solo");
const isSolo = computed(() => selectedMode.value === "solo");
const handleModeChange = (event: Event) => {
    selectedMode.value = ((event.target as HTMLSelectElement).value) as Gamemode;
    updateLeaderboard();
};

const selectedCountry = ref('');
const handleCountryFilterChange = (event: { country: string }) => {
    selectedCountry.value = event.country;
    updateLeaderboard();
};

const updateLeaderboard = async () => {
    const mode = selectedMode.value;
    const country = selectedCountry.value;

    if (dataCache.value[selectedOrder.value][selectedMode.value][selectedCountry.value]?.length as number > 0) {
        rateables.value = dataCache.value[selectedOrder.value][selectedMode.value][selectedCountry.value];
        return;
    }

    if (country === "" && mode === "solo" && dataCache.value[selectedOrder.value].solo.all.length > 0) {
        rateables.value = dataCache.value[selectedOrder.value].solo.all;
        return;
    }

    if (mode === "team" && dataCache.value[selectedOrder.value].team.all.length > 0) {
        rateables.value = dataCache.value[selectedOrder.value].team.all;
        return;
    }

    const cacheKey = country || 'all';
    if (
        (isSortOrder(mode) && dataCache.value[selectedOrder.value].solo[cacheKey] !== undefined) ||
        (isSortOrder(mode) && dataCache.value[selectedOrder.value].team[cacheKey] !== undefined)
    ) {
        rateables.value = (dataCache.value[selectedOrder.value][mode][cacheKey] as Rateable[]);
        return;
    }

    try {
        loading.value = true;
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

        rateables.value = json || [];
    } catch (error) {
        console.error("Error fetching leaderboard data:", error);
        rateables.value = [];
    } finally {
        setTimeout(() => loading.value = false, 300);
    }
};

watch(() => props.playersOrTeams, (newPlayers) => {
    if (newPlayers && newPlayers.length > 0 && !isTeam(newPlayers[0])) {
        dataCache.value[selectedOrder.value].solo.all = newPlayers as Player[];

        if (selectedMode.value === "solo" && !selectedCountry.value) {
            rateables.value = newPlayers;
        }
    }
}, {deep: true});

const playersOrTeamsToLeaderboardRows = (playerOrTeam: Player | Team): LeaderboardRow => {
    return isTeam(playerOrTeam) ? {
        id: playerOrTeam.id,
        geoGuessrId: playerOrTeam.team_id,
        name: playerOrTeam.name,
        rating: playerOrTeam.rating,
        countryCodes: [playerOrTeam.player_a.country_code, playerOrTeam.player_b.country_code],
        players: [playerOrTeam.player_a, playerOrTeam.player_b],
        isPlaceholder: false,
        type: 'team'
    } : {
        id: playerOrTeam.id,
        geoGuessrId: playerOrTeam.user_id,
        name: playerOrTeam.name,
        rating: playerOrTeam.rating,
        countryCodes: [playerOrTeam.country_code],
        isPlaceholder: false,
        type: 'player'
    }
}

const leaderboardRows = computed<LeaderboardRow[]>(() => {
    const rows: LeaderboardRow[] = [...rateables.value.map(playersOrTeamsToLeaderboardRows)];
    const placeholderCount = Math.max(0, 10 - rows.length);

    for (let i = 0; i < placeholderCount; i++) {
        rows.push({
            id: `placeholder-${i}`,
            geoGuessrId: `placeholder-${i}`,
            name: '',
            rating: 0,
            countryCodes: [],
            isPlaceholder: true,
            type: 'player',
        });
    }

    return rows;
});

const handlePlayerClick = (playerOrTeam: LeaderboardRow) => {
    emit("playerClick", {rateable: playerOrTeam})
};

const selectedOrder = ref<SortOrder>("desc");
const handleSortOrderChange = (event: Event) => {
    selectedOrder.value = ((event.target as HTMLSelectElement).value as SortOrder);
    updateLeaderboard();
};

onMounted(() => {
    if (props.playersOrTeams && props.playersOrTeams.length > 0) {
        dataCache.value[selectedOrder.value].solo.all = props.playersOrTeams as Player[];
    }
});

</script>
