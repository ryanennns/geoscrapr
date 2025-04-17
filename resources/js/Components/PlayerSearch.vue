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
                placeholder="Search for a player name or ID..."
                class="pl-10 pr-10 py-3 border border-gray-300 rounded-lg shadow-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
            />
            <transition name="fade">
                <button
                    v-if="searchQuery"
                    @click="
                        () => {
                            searchQuery = '';
                            searchResults = [];
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
            v-if="searchResults.length && showDropdown"
            class="absolute top-full left-0 z-10 bg-gray-50 w-full shadow-md max-h-64 overflow-y-auto rounded-lg mt-1"
        >
            <PlayerSearchResult
                v-for="player in searchResults"
                :key="player.id"
                :player="player"
                @player-click="handlePlayerClick"
            />
        </ul>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import PlayerSearchResult from "./PlayerSearchResult.vue";
import type { Player } from "@/Types/core.ts";

const searchInputElement = ref<HTMLInputElement>();
const searchQuery = ref<string>("");
const searchResults = ref<Player[]>([]);
const showDropdown = ref<boolean>(true);

const searchCache = ref<Record<string, Player[]>>({});

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;
const fetchPlayers = () => {
    showDropdown.value = true;

    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }

    if (searchCache.value[searchQuery.value]) {
        searchResults.value = searchCache.value[searchQuery.value];

        return;
    }

    debounceTimeout = setTimeout(async () => {
        if (searchQuery.value.length < 2) {
            searchResults.value = [];
            return;
        }

        try {
            const response = await fetch(
                `/players/search?q=${encodeURIComponent(searchQuery.value)}`,
            );
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }

            const json = await response.json();

            searchCache.value[searchQuery.value] = json.data;

            searchResults.value = json.data;
        } catch (err) {
            console.error("Player search failed:", err);
        }
    }, 300);
};

const emit = defineEmits(["playerClick"]);
const handlePlayerClick = async (player: Player) => {
    searchInputElement.value?.blur();
    emit("playerClick", player);
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
