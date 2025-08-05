import type {
    GameType,
    Player,
    Rateable,
    RateableType,
    RatingChange,
    Snapshot,
    Team,
} from "@/Types/core.ts";
import type { CountryCode } from "@/Composables/usePlayerUtils.ts";

export interface ApiResponse<T> {
    data?: T;
    error?: {};
}

export type RateableHistoryApiResponse = ApiResponse<RatingChange[]>;

export type GetLastUpdatedApiResponse = ApiResponse<{ date: string }>;

export type GetSnapshotByDateApiResponse = ApiResponse<{
    solo: Snapshot;
    team: Snapshot;
}>;

export type GetAvailableCountriesApiResponse = ApiResponse<CountryCode[]>;

export type GetRateablesApiResponse = ApiResponse<Rateable[]>;

interface GeoGuessrNumericType {
    amount: number;
    unit: string;
    percentage: number;
}

export type GetInDepthPlayerDataApiResponse = ApiResponse<{
    gamesPlayed: number;
    roundsPlayed: number;
    maxGameScore: GeoGuessrNumericType;
    averageGameScore: GeoGuessrNumericType;
    maxRoundScore: GeoGuessrNumericType;
    streakGamesPlayed: number;
    averageDistance: any;
    averageTime: number;
    timedOutGuesses: any;
    division: string;
}>;

export type GetRateableApiResponse = ApiResponse<Player | Team>;

export function useApiClient() {
    const getRateableHistory = async (
        rateableType: RateableType,
        rateableId: string,
    ): Promise<RateableHistoryApiResponse> => {
        const response = await fetch(`/${rateableType}s/history/${rateableId}`);

        if (!response.ok) {
            return {
                error: {
                    statusCode: response.status,
                },
            };
        }

        const json = await response.json();

        return {
            data: json.data,
        };
    };

    const getLastUpdated = async (): Promise<GetLastUpdatedApiResponse> => {
        const response = await fetch("/last-updated");

        if (!response.ok) {
            return {
                error: response.status,
            };
        }

        const json = await response.json();

        return {
            data: {
                date: json.date,
            },
        };
    };

    const getSnapshotForDate = async (
        date: string,
    ): Promise<GetSnapshotByDateApiResponse> => {
        const response = await fetch(`/snapshots?date=${date}`);

        if (!response.ok) {
            return {
                error: response.status,
            };
        }

        const data = await response.json();
        return {
            data: data.data,
        };
    };

    interface GetRateablesInput {
        playersOrTeams: "players" | "teams";
        active?: string;
        country?: string;
        order?: string;
        gameType?: GameType;
    }
    const getRateables = async ({
        playersOrTeams,
        active,
        country,
        order,
        gameType,
    }: GetRateablesInput): Promise<GetRateablesApiResponse> => {
        const params = new URLSearchParams();

        if (active && active === "active") {
            params.append("active", "1");
        }

        if (country && country !== "all") {
            params.append("country", country);
        }

        if (order) {
            params.append("order", order);
        }

        if (gameType && gameType !== "all") {
            params.append("game_type", gameType);
        }

        const response = await fetch(
            `/${playersOrTeams}?${params.toString()}`,
            {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
            },
        );

        if (!response.ok) {
            return {
                error: response.status,
            };
        }

        const json = await response.json();

        return {
            data: json.data || [],
        };
    };

    const getAvailableCountries =
        async (): Promise<GetAvailableCountriesApiResponse> => {
            const response = await fetch("countries");

            if (!response.ok) {
                return {
                    error: response.status,
                };
            }

            return {
                data: (await response.json())?.data ?? [],
            };
        };

    const getInDepthPlayerData =
        async (): Promise<GetInDepthPlayerDataApiResponse> => {
            throw new Error("not implemented");
        };

    const getRateable = async (id: string): Promise<GetRateableApiResponse> => {
        const response = await fetch(`rateables?id=${id}`);

        return await response.json();
    };

    return {
        getRateableHistory,
        getLastUpdated,
        getSnapshotForDate,
        getRateables,
        getAvailableCountries,
        getInDepthPlayerData,
        getRateable,
    };
}
