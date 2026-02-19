<script setup lang="ts">
import RatingBadge from "@/Components/RatingBadge.vue";
import Flag from "@/Components/Flag.vue";
import type { LeaderboardRow } from "@/Types/core.ts";
import { usePlayerUtils } from "@/Composables/usePlayerUtils.ts";
import { useBrowserUtils } from "@/Composables/useBrowserUtils.ts";
import type { MatchHistory } from "@/Composables/useApiClient.ts";
import { computed } from "vue";

interface Props {
    leaderboardRow: LeaderboardRow;
    loadingMatchHistory: boolean;
    expanded: boolean;
    matchHistory: MatchHistory[];
    colour: string;
    comparing: boolean;
}

const { generateProfileUrl } = usePlayerUtils();
const { isMobile } = useBrowserUtils();

const props = defineProps<Props>();

const allowedNameLength = computed<number>(() => {
    let numberOfNullRatings = 0;

    if (!props.leaderboardRow.moving_rating) {
        numberOfNullRatings += 1;
    }

    if (!props.leaderboardRow.no_move_rating) {
        numberOfNullRatings += 1;
    }

    if (!props.leaderboardRow.nmpz_rating) {
        numberOfNullRatings += 1;
    }

    return 13 + numberOfNullRatings * 2;
});

const croppedName = computed<string>(() => {
    if (props.expanded) {
        return props.leaderboardRow.name.trim();
    }

    if (props.leaderboardRow.name.trim().length <= allowedNameLength.value) {
        return props.leaderboardRow.name.trim();
    }

    return (
        props.leaderboardRow.name.trim().slice(0, allowedNameLength.value - 3) +
        "..."
    );
});
</script>

<template>
    <div>
        <span class="text-xl font-bold flex items-center mb-2 gap-1">
            <span v-for="countryCode in props.leaderboardRow.countryCodes">
                <Flag
                    :country-code="countryCode"
                    dimensions="120x90"
                    class="mr-1"
                    width="20"
                    height="15"
                />
            </span>
            <p
                :style="{ color: comparing ? colour : 'inherit' }"
                class="italic"
            >
                {{ croppedName }}
            </p>
            <p class="ml-1 italic font-light">
                {{ props.leaderboardRow.rating }},
                <span class="non-italic">#{{ props.leaderboardRow.rank }}</span>
            </p>
            <div
                v-if="!comparing"
                class="hidden sm:flex flex-wrap gap-2 items-center ml-4"
            >
                <RatingBadge
                    v-show="props.leaderboardRow.moving_rating"
                    label="M: "
                    :text="`${props.leaderboardRow.moving_rating}`"
                />
                <RatingBadge
                    v-show="props.leaderboardRow.no_move_rating"
                    label="NM: "
                    :text="`${props.leaderboardRow.no_move_rating}`"
                />
                <RatingBadge
                    v-show="props.leaderboardRow.nmpz_rating"
                    label="NMPZ: "
                    :text="`${props.leaderboardRow.nmpz_rating}`"
                />
            </div>
        </span>

        <span
            class="flex items-center gap-2"
            v-if="!props.leaderboardRow.players"
        >
        </span>

        <span v-if="props.leaderboardRow.players" class="flex">
            <a
                :href="
                    generateProfileUrl(props.leaderboardRow.players[0]?.user_id)
                "
                target="_blank"
                class="mr-1"
            >
                <p
                    class="text-gray-600 dark:text-gray-400 font-mono underline font-light"
                >
                    {{ props.leaderboardRow.players[0]?.name }}
                </p>
            </a>
            &
            <a
                :href="
                    generateProfileUrl(props.leaderboardRow.players[1]?.user_id)
                "
                target="_blank"
                class="ml-1"
            >
                <p
                    class="text-gray-600 dark:text-gray-400 font-mono underline font-light"
                >
                    {{ props.leaderboardRow.players[1]?.name }}
                </p>
            </a>
        </span>
        <div v-else class="flex items-center mb-2 w-full">
            <a
                :href="generateProfileUrl(props.leaderboardRow.geoGuessrId)"
                target="_blank"
            >
                <p
                    class="text-gray-600 dark:text-gray-400 font-mono underline font-light"
                >
                    {{ props.leaderboardRow.geoGuessrId }}
                </p>
            </a>

            <div
                v-if="
                    false &&
                    matchHistory.length &&
                    !isMobile &&
                    !loadingMatchHistory &&
                    expanded
                "
                class="flex gap-1 ml-auto"
            >
                Recent Matches:
                <div v-for="match in matchHistory">
                    <a
                        :href="`https://geoguessr.com/duels/${match.id}`"
                        target="_blank"
                    >
                        <div v-if="match.winner === leaderboardRow.id">🟢</div>
                        <div v-else>🔴</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
