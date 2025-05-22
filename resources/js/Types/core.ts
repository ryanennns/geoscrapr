import type { CountryCode } from "@/composables/usePlayerUtils.ts";

export interface Player {
    id: string;
    user_id: string;
    name: string;
    rating: number | null;
    country_code: CountryCode;
    created_at: string;
    updated_at: string;
}

export interface Team {
    id: string;
    team_id: string;
    name: string;
    rating: number;
    player_a: Player;
    player_b: Player;
}

export type Rateable = Player | Team;

export interface LeaderboardRow {
    id: string;
    geoGuessrId: string;
    name: string;
    rating: number | null;
    countryCodes: CountryCode[];
    players?: Player[];
    isPlaceholder: boolean;
    type: "player" | "team";
}

export const EMPTY_LEADERBOARD_ROW: LeaderboardRow = {
    id: "",
    geoGuessrId: "",
    name: "",
    rating: 0,
    countryCodes: [],
    isPlaceholder: true,
    type: "player",
};

export interface RatingChange {
    id: string;
    rating: number;
    created_at: string;
}

export interface Snapshot {
    date: string;
    buckets: {
        [range: string]: number;
    };
    n: number;
    type: string;
}

export const isTeam = (maybeTeam: any): maybeTeam is Team =>
    maybeTeam.player_a && maybeTeam.player_b;
