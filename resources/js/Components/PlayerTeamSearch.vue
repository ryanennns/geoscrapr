<template>
    <div class="relative w-full max-w-md">
        <div class="relative">
            <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
            >
                <svg
                    class="h-5 w-5 text-gray-400"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
            <input
                type="text"
                ref="searchInputElement"
                v-model="searchQuery"
                @input="fetchPlayers"
                @focus="fetchPlayers"
                @blur="() => (showDropdown = false)"
                :placeholder="placeholder"
                class="pl-10 pr-10 py-3 border border-gray-300 rounded-lg shadow-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
            />
            <transition name="fade">
                <button
                    v-if="searchQuery"
                    @click="
                        () => {
                            searchQuery = '';
                            playerSearchResults = [];
                        }
                    "
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                >
                    <svg
                        class="h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </transition>
        </div>

        <ul
            v-if="
                (playerSearchResults.length || teamSearchResults.length) &&
                showDropdown
            "
            class="absolute top-full left-0 z-10 bg-gray-50 w-full shadow-md max-h-64 overflow-y-auto rounded-lg mt-1"
        >
            <li
                class="pl-3 p-2 bg-gray-300"
                v-show="playerSearchResults.length > 0 && showPlayers"
            >
                Players
            </li>
            <PlayerSearchResult
                v-if="showPlayers"
                v-for="player in playerSearchResults"
                :key="player.id"
                :player="player"
                @player-click="handlePlayerOrTeamClicked"
            />

            <li
                class="pl-3 p-2 bg-gray-300"
                v-show="teamSearchResults.length > 0 && showTeams"
            >
                Teams
            </li>
            <TeamSearchResult
                v-show="showTeams"
                v-for="team in teamSearchResults"
                :key="team.id"
                :team="team"
                @team-click="handlePlayerOrTeamClicked"
            />
        </ul>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import PlayerSearchResult from "./PlayerSearchResult.vue";
import type { Player, Rateable, Team } from "@/Types/core.ts";
import { usePlayerUtils } from "@/Composables/usePlayerUtils.js";
import TeamSearchResult from "@/Components/TeamSearchResult.vue";

interface Props {
    placeholder?: string;
    showPlayers?: boolean;
    showTeams?: boolean;
}

const {
    placeholder = "Search for a player, team, or ID...",
    showPlayers = true,
    showTeams = true,
} = defineProps<Props>();

const { rateableToLeaderboardRows } = usePlayerUtils();

const searchInputElement = ref<HTMLInputElement>();
const searchQuery = ref<string>("");
const playerSearchResults = ref<Player[]>([]);
const teamSearchResults = ref<Team[]>([]);
const showDropdown = ref<boolean>(true);

const playerSearchCache = ref<Record<string, Player[]>>({});
const teamSearchCache = ref<Record<string, Team[]>>({});

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;
const fetchPlayers = () => {
    showDropdown.value = true;

    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }

    if (
        playerSearchCache.value[searchQuery.value] &&
        teamSearchCache.value[searchQuery.value]
    ) {
        playerSearchResults.value = playerSearchCache.value[searchQuery.value];
        teamSearchResults.value = teamSearchCache.value[searchQuery.value];

        return;
    }

    debounceTimeout = setTimeout(async () => {
        if (searchQuery.value.length < 2) {
            playerSearchResults.value = [];
            teamSearchResults.value = [];
            return;
        }

        try {
            const response = await fetch(
                `/search?q=${encodeURIComponent(searchQuery.value)}`,
            );
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }

            const json = await response.json();

            const players = json.data.players;
            const teams = json.data.teams;

            playerSearchCache.value[searchQuery.value] = players;
            teamSearchCache.value[searchQuery.value] = teams;

            playerSearchResults.value = players;
            teamSearchResults.value = teams;
        } catch (err) {
            console.error("Player search failed:", err);
        }
    }, 300);
};

const emit = defineEmits(["rowClicked"]);
const handlePlayerOrTeamClicked = async (rateable: Rateable) => {
    searchInputElement.value?.blur();
    emit("rowClicked", {
        rateable: rateableToLeaderboardRows(rateable),
    });
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
