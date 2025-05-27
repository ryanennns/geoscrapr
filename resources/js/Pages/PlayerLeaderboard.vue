<template>
    <div class="mt-6 md:mt-10">
        <div class="bg-white p-4 md:p-6 rounded-xl shadow-md">
            <div
                class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 gap-3"
            >
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                    Rating Leaderboard
                </h2>
                <div class="flex flex-wrap gap-2 sm:gap-4">
                    <div class="relative">
                        <CountryDropdown
                            @change="handleCountryFilterChange"
                            :disabled="!isSolo"
                        />
                    </div>
                    <Toggle
                        data-testid="mode-toggle"
                        v-model="selectedMode"
                        :options="modeOptions"
                        color="blue"
                        @update:modelValue="updateLeaderboard"
                    />

                    <Toggle
                        data-testid="order-toggle"
                        v-model="selectedOrder"
                        :options="sortOptions"
                        color="green"
                        @update:modelValue="updateLeaderboard"
                    />

                    <Toggle
                        data-testid="active-toggle"
                        v-model="isActive"
                        :options="activeOptions"
                        color="indigo"
                        @update:modelValue="updateLeaderboard"
                    />

                    <Toggle
                        data-testid="game-mode-toggle"
                        :options="gameModeOptions"
                        color="red"
                        class="opacity-40"
                        v-model="selectedGameMode"
                    />
                </div>
            </div>
            <div class="overflow-x-auto -mx-4 md:mx-0">
                <LeaderboardLoadingSkeleton
                    v-show="loading"
                    :is-solo="isSolo"
                />
                <table
                    v-show="!loading"
                    class="min-w-full divide-y divide-gray-200 table-fixed"
                >
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                scope="col"
                                class="w-1/12 px-2 sm:px-4 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
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
                        <tr
                            v-for="(leaderboardRow, index) in leaderboardRows"
                            :key="index"
                            :class="
                                leaderboardRow.isPlaceholder
                                    ? 'opacity-50'
                                    : 'hover:bg-indigo-50 transition-colors cursor-pointer'
                            "
                            @click="
                                leaderboardRow.isPlaceholder
                                    ? null
                                    : handlePlayerClick(leaderboardRow)
                            "
                            :data-testid="`row-${index}`"
                        >
                            <td
                                class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap"
                            >
                                <div class="text-xs sm:text-sm font-medium">
                                    {{ index + 1 }}
                                </div>
                            </td>
                            <td
                                class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap"
                            >
                                <div
                                    class="text-xs sm:text-sm font-medium text-gray-900 truncate max-w-full"
                                >
                                    {{ leaderboardRow.name || "-" }}
                                </div>
                            </td>
                            <td
                                class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap"
                            >
                                <div class="flex items-center">
                                    <div
                                        v-for="countryCode in leaderboardRow.countryCodes"
                                        class="flex"
                                    >
                                        <Flag
                                            :country-code="countryCode"
                                            dimensions="120x90"
                                            class="mr-1"
                                            width="24"
                                            height="18"
                                            :class="{ 'sm:w-8 sm:h-6': true }"
                                        />
                                    </div>
                                </div>
                            </td>
                            <td
                                class="px-2 sm:px-4 md:px-6 py-2 md:py-4 whitespace-nowrap"
                            >
                                <div
                                    class="text-xs sm:text-sm font-semibold text-indigo-700"
                                >
                                    {{
                                        leaderboardRow.isPlaceholder ||
                                        leaderboardRow.rating === null
                                            ? "-"
                                            : leaderboardRow.rating?.toLocaleString()
                                    }}
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
import { computed, onMounted, ref, watch } from "vue";
import CountryDropdown from "@/Components/CountryDropdown.vue";
import LeaderboardLoadingSkeleton from "@/Components/LeaderboardLoadingSkeleton.vue";
import Flag from "@/Components/Flag.vue";
import Toggle from "@/Components/Toggle.vue";
import {
    isTeam,
    type Player,
    type LeaderboardRow,
    type Rateable,
} from "@/Types/core.ts";
import { usePlayerUtils } from "@/Composables/usePlayerUtils.js";
import { useApiClient } from "@/Composables/useApiClient.ts";

const { getRateables } = useApiClient();
const { rateableToLeaderboardRows } = usePlayerUtils();

const sortOrders = ["asc", "desc"] as const;
type SortOrder = (typeof sortOrders)[number];

interface Props {
    playersOrTeams: Rateable[];
}

const props = defineProps<Props>();

type IsActive = "active" | "all";

interface SubCache {
    [key: string]: Rateable[];
}

type PlayerTeamCache = Record<
    IsActive,
    Record<SortOrder, Record<Gamemode, SubCache>>
>;

const emit = defineEmits(["playerClick", "countryFilterChange"]);

const createCacheRoot = () => ({
    solo: { all: [] },
    team: { all: [] },
});

const dataCache = ref<PlayerTeamCache>({
    active: {
        asc: createCacheRoot(),
        desc: createCacheRoot(),
    },
    all: {
        asc: createCacheRoot(),
        desc: createCacheRoot(),
    },
});

const rateables = ref<Rateable[]>(props.playersOrTeams);

const isSolo = computed(() => selectedMode.value === "solo");

type Gamemode = "solo" | "team";
const selectedMode = ref<Gamemode>("solo");
const modeOptions = [
    { label: "Solo", value: "solo" },
    { label: "Team", value: "team" },
];

const selectedOrder = ref<SortOrder>("desc");
const sortOptions = [
    { label: "🔽 Desc", value: "desc" },
    { label: "🔼 Asc", value: "asc" },
];

const isActive = ref<IsActive>("all");
const activeOptions = [
    { label: "All", value: "all" },
    { label: "Active", value: "active" },
];

const selectedGameMode = ref("all");
const gameModeOptions = [
    { label: "All", value: "all" },
    { label: "Moving", value: "moving" },
    { label: "No Move", value: "no_move" },
    { label: "NMPZ", value: "nmpz" },
];

const selectedCountry = ref("");
const handleCountryFilterChange = (event: { country: string }) => {
    selectedCountry.value = event.country;
    updateLeaderboard();
};

const loading = ref(false);
const updateLeaderboard = async () => {
    const active = isActive.value;
    const order = selectedOrder.value;
    const mode = selectedMode.value;
    const country = selectedCountry.value || "all";

    if (dataCache.value[active][order][mode][country]?.length > 0) {
        rateables.value = dataCache.value[active][order][mode][
            country
        ] as Rateable[];
        return;
    }

    loading.value = true;
    const rateablesResponse = await getRateables({
        playersOrTeams: mode === "solo" ? "players" : "teams",
        active,
        country,
        order,
    });

    if (rateablesResponse.error && rateablesResponse.data === undefined) {
        console.error("Error fetching leaderboard data");
        rateables.value = [];
        return;
    }

    dataCache.value[active][order][mode][country] =
        rateablesResponse.data ?? [];
    rateables.value = rateablesResponse.data ?? [];
    console.log(rateables.value);
    setTimeout(() => (loading.value = false), 300);
};

watch(
    () => props.playersOrTeams,
    (newPlayers) => {
        if (newPlayers && newPlayers.length > 0 && !isTeam(newPlayers[0])) {
            dataCache.value[isActive.value][selectedOrder.value].solo.all =
                newPlayers as Player[];

            if (selectedMode.value === "solo" && !selectedCountry.value) {
                rateables.value = newPlayers;
            }
        }
    },
    { deep: true },
);

const leaderboardRows = computed<LeaderboardRow[]>(() => {
    const rows: LeaderboardRow[] = [
        ...rateables.value.map(rateableToLeaderboardRows),
    ];

    const maybeCountryCode = rows[0]?.countryCodes[0] ?? "";

    const placeholderCount = Math.max(0, 10 - rows.length);

    for (let i = 0; i < placeholderCount; i++) {
        rows.push({
            id: `placeholder-${i}`,
            geoGuessrId: `placeholder-${i}`,
            name: "",
            rating: 0,
            moving_rating: null,
            no_move_rating: null,
            nmpz_rating: null,
            countryCodes: [maybeCountryCode],
            isPlaceholder: true,
            type: "player",
        });
    }

    return rows;
});

const handlePlayerClick = (playerOrTeam: LeaderboardRow) => {
    emit("playerClick", { rateable: playerOrTeam });
};

onMounted(() => {
    if (props.playersOrTeams && props.playersOrTeams.length > 0) {
        dataCache.value[isActive.value][selectedOrder.value].solo.all =
            props.playersOrTeams as Player[];
    }
});
</script>
