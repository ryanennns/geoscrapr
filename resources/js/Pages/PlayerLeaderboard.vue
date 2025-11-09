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
                            v-model="selectedCountry"
                            @update:modelValue="updateLeaderboard"
                            :disabled="!isSolo"
                        />
                    </div>
                    <Toggle
                        data-testid="game-type-toggle"
                        :options="gameTypeOptions"
                        color="red"
                        v-model="selectedGameType"
                        :disabled="!isSolo"
                        @update:modelValue="updateLeaderboard"
                    />

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

                    <ResetButton @click="resetFilters" />
                </div>
            </div>
            <div class="overflow-x-auto -mx-4 md:mx-0">
                <LeaderboardLoadingSkeleton
                    v-show="loading"
                    :is-solo="isSolo"
                    :rating-header="ratingHeader"
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
                                {{ ratingHeader }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <Row
                            v-for="(leaderboardRow, index) in leaderboardRows"
                            :key="leaderboardRow.id"
                            :leaderboard-row="leaderboardRow"
                            :selected-game-type="selectedGameType"
                            :number="(rateablesPage - 1) * 10 + index + 1"
                            @player-click="handlePlayerClick"
                        />
                    </tbody>
                </table>
            </div>

            <PaginationControls
                @page-changed="updateLeaderboard"
                v-model="rateablesPage"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from "vue";
import CountryDropdown from "@/Components/CountryDropdown.vue";
import LeaderboardLoadingSkeleton from "@/Components/LeaderboardLoadingSkeleton.vue";
import Toggle from "@/Components/Toggle.vue";
import {
    type GameType,
    isTeam,
    type LeaderboardRow,
    type Player,
    type Rateable,
} from "@/Types/core.ts";
import { CountryCode, usePlayerUtils } from "@/Composables/usePlayerUtils.js";
import { useApiClient } from "@/Composables/useApiClient.ts";
import PaginationControls from "@/Components/PaginationControls.vue";
import Row from "@/Pages/Row.vue";
import ResetButton from "@/Components/ResetButton.vue";
import { useUrlParams } from "@/Composables/useUrlParams.ts";

const { getRateables } = useApiClient();
const { rateableToLeaderboardRows } = usePlayerUtils();

const ratingHeader = computed<string>(() => {
    switch (selectedGameType.value) {
        case "moving":
            return "Moving Rating";
        case "no_move":
            return "No Move Rating";
        case "nmpz":
            return "NMPZ Rating";
        default:
            return "Rating";
    }
});

const sortOrders = ["asc", "desc"] as const;
type SortOrder = (typeof sortOrders)[number];

interface Props {
    playersOrTeams: Rateable[];
}

const props = defineProps<Props>();

type IsActive = "active" | "all";

type PageCache = Record<number, Rateable[]>;
type CountryCache = Record<string, PageCache>;

type PlayerTeamBranch = {
    solo: CountryCache;
    team: CountryCache;
};

type ModeCache = {
    asc: PlayerTeamBranch;
    desc: PlayerTeamBranch;
};

type ActiveCache = {
    active: ModeCache;
    all: ModeCache;
};

type PlayerTeamCache = Record<GameType, ActiveCache>;

const emit = defineEmits(["playerClick"]);

const createPlayerTeamBranch = (): PlayerTeamBranch => ({
    solo: { all: {} }, // country -> page -> data
    team: { all: {} },
});

const createModeCache = (): ModeCache => ({
    asc: createPlayerTeamBranch(),
    desc: createPlayerTeamBranch(),
});

const createActiveCache = (): ActiveCache => ({
    active: createModeCache(),
    all: createModeCache(),
});

const dataCache = ref<PlayerTeamCache>({
    all: createActiveCache(),
    moving: createActiveCache(),
    no_move: createActiveCache(),
    nmpz: createActiveCache(),
});

const rateables = ref<Rateable[]>(props.playersOrTeams);

type Gamemode = "solo" | "team";
const selectedMode = ref<Gamemode>("solo");
const modeOptions = [
    { label: "Solo", value: "solo" },
    { label: "Team", value: "team" },
];
watch(selectedMode, (newMode) => {
    if (newMode === "team") {
        selectedGameType.value = "all";
    }
});

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

const selectedGameType = ref<GameType>("all");
const gameTypeOptions = [
    { label: "All", value: "all" },
    { label: "Moving", value: "moving" },
    { label: "No Move", value: "no_move" },
    { label: "NMPZ", value: "nmpz" },
];
watch(selectedGameType, () => {
    if (selectedMode.value === "team") {
        selectedGameType.value = "all";
    }
});

const resetFilters = () => {
    selectedGameType.value = "all";
    selectedMode.value = "solo";
    selectedOrder.value = "desc";
    isActive.value = "all";
    selectedCountry.value = "";
    rateablesPage.value = 1;
    updateLeaderboard();
};

const isSolo = computed(() => selectedMode.value === "solo");

const selectedCountry = ref<CountryCode | "">("");

const loading = ref(false);
const rateablesPage = ref<number>(1);

const getCacheBucket = () => {
    const gt = selectedGameType.value;
    const act = isActive.value;
    const ord = selectedOrder.value;
    const mode = selectedMode.value;
    const country = selectedCountry.value || "all";
    return {
        bucket: dataCache.value[gt][act][ord][mode],
        country,
        page: rateablesPage.value,
    };
};

const readFromCache = (): Rateable[] | undefined => {
    const { bucket, country, page } = getCacheBucket();
    const countryCache = bucket[country] ?? {};
    return countryCache[page];
};

const writeToCache = (rows: Rateable[]) => {
    const { bucket, country, page } = getCacheBucket();
    if (!bucket[country]) bucket[country] = {};
    bucket[country][page] = rows;
};

const updateLeaderboard = async () => {
    await nextTick();

    const cached = readFromCache();
    if (cached && cached.length > 0) {
        rateables.value = cached;
        return;
    }

    loading.value = true;
    const rateablesResponse = await getRateables({
        playersOrTeams: selectedMode.value === "solo" ? "players" : "teams",
        active: isActive.value,
        country: selectedCountry.value || "all",
        order: selectedOrder.value,
        gameType: selectedGameType.value,
        page: rateablesPage.value,
    });

    if (rateablesResponse.error && rateablesResponse.data === undefined) {
        console.error("Error fetching leaderboard data");
        rateables.value = [];
        loading.value = false;
        return;
    }

    const data = rateablesResponse.data ?? [];
    writeToCache(data);
    rateables.value = data;
    setTimeout(() => (loading.value = false), 300);
};

watch(
    () => props.playersOrTeams,
    (newPlayers) => {
        if (newPlayers && newPlayers.length > 0 && !isTeam(newPlayers[0])) {
            // seed page 1 of "solo/all" with initial props
            const branch =
                dataCache.value[selectedGameType.value][isActive.value][
                    selectedOrder.value
                ].solo;
            if (!branch.all) branch.all = {};
            branch.all[1] = newPlayers as Player[];

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

const GAME_TYPE_URL_PARAM = "game_type";
const GAME_MODE_URL_PARAM = "mode";
const ORDER_URL_PARAM = "order";
const COUNTRY_URL_PARAM = "country";
const ACTIVE_URL_PARAM = "active";
onMounted(async () => {
    await new Promise((resolve) => setTimeout(resolve, 250)); // wait for next tick

    console.log('onMounted - checking URL params for leaderboard filters',
        {
            GAME_TYPE_URL_PARAM: get(GAME_TYPE_URL_PARAM),
            GAME_MODE_URL_PARAM: get(GAME_MODE_URL_PARAM),
            ORDER_URL_PARAM: get(ORDER_URL_PARAM),
            COUNTRY_URL_PARAM: get(COUNTRY_URL_PARAM),
            ACTIVE_URL_PARAM: get(ACTIVE_URL_PARAM),
        })

    if (get(GAME_TYPE_URL_PARAM)) {
        console.log(`found GAME_TYPE_URL_PARAM, setting ${get(GAME_TYPE_URL_PARAM)}`)
        selectedGameType.value = get(GAME_TYPE_URL_PARAM) as GameType;
    }

    if (get(GAME_MODE_URL_PARAM)) {
        console.log(`found GAME_MODE_URL_PARAM, setting ${get(GAME_MODE_URL_PARAM)}`)
        selectedMode.value = get(GAME_MODE_URL_PARAM) as Gamemode;
    }

    if (get(ORDER_URL_PARAM)) {
        console.log(`found ORDER_URL_PARAM, setting ${get(ORDER_URL_PARAM)}`)
        selectedOrder.value = get(ORDER_URL_PARAM) as SortOrder;
    }

    if (get(COUNTRY_URL_PARAM)) {
        console.log(`found COUNTRY_URL_PARAM, setting ${get(COUNTRY_URL_PARAM)}`)
        selectedCountry.value = get(COUNTRY_URL_PARAM) as CountryCode;
    }

    if (get(ACTIVE_URL_PARAM)) {
        console.log(`found ACTIVE_URL_PARAM, setting ${get(ACTIVE_URL_PARAM)}`)
        isActive.value = get(ACTIVE_URL_PARAM) as IsActive;
    }

    if (props.playersOrTeams && props.playersOrTeams.length > 0) {
        const branch =
            dataCache.value[selectedGameType.value][isActive.value][
                selectedOrder.value
            ].solo;
        if (!branch.all) {
            branch.all = {};
        }
        branch.all[1] = props.playersOrTeams as Player[];
    }
});

const { set, clear, get } = useUrlParams();

watch(
    () => [
        selectedGameType.value,
        selectedMode.value,
        selectedOrder.value,
        isActive.value,
        selectedCountry.value,
    ],
    () => {
        rateablesPage.value = 1;

        selectedGameType.value && selectedGameType.value !== "all"
            ? set(GAME_TYPE_URL_PARAM, selectedGameType.value)
            : clear(GAME_TYPE_URL_PARAM);
        selectedMode.value && selectedMode.value !== "solo"
            ? set(GAME_MODE_URL_PARAM, selectedMode.value)
            : clear(GAME_MODE_URL_PARAM);
        selectedOrder.value && selectedOrder.value !== "desc"
            ? set(ORDER_URL_PARAM, selectedOrder.value)
            : clear(ORDER_URL_PARAM);
        selectedCountry.value
            ? set(COUNTRY_URL_PARAM, selectedCountry.value)
            : clear(COUNTRY_URL_PARAM);
        isActive.value && isActive.value !== "all"
            ? set(ACTIVE_URL_PARAM, isActive.value)
            : clear(ACTIVE_URL_PARAM);
    },
    { immediate: true },
);
</script>
