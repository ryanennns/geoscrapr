import type { CountryCode } from "@/Composables/usePlayerUtils.ts";

export type GameType = "all" | "moving" | "no_move" | "nmpz";

export interface Player {
    id: string;
    user_id: string;
    name: string;
    rating: number | null;
    moving_rating: number | null;
    no_move_rating: number | null;
    nmpz_rating: number | null;
    country_code: CountryCode;
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

export type RateableType = "player" | "team";

export interface LeaderboardRow {
    id: string;
    geoGuessrId: string;
    name: string;
    rating: number | null;
    moving_rating: number | null;
    no_move_rating: number | null;
    nmpz_rating: number | null;
    countryCodes: CountryCode[];
    players?: Player[];
    isPlaceholder: boolean;
    type: RateableType;
}

export const EMPTY_LEADERBOARD_ROW: LeaderboardRow = {
    id: "",
    geoGuessrId: "",
    name: "",
    rating: 0,
    moving_rating: null,
    no_move_rating: null,
    nmpz_rating: null,
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
