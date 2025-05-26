import type { RateableType, RatingChange, Snapshot } from "@/Types/core.ts";

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
        date: Date,
    ): Promise<GetSnapshotByDateApiResponse> => {
        const response = await fetch(
            `/snapshots?date=${date.toISOString().split("T")[0]}`,
        );

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

    return {
        getRateableHistory,
        getLastUpdated,
        getSnapshotForDate,
    };
}
