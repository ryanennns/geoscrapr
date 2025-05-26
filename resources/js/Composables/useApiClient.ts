import type { RateableType, RatingChange } from "@/Types/core.ts";

export interface RateableHistoryApiResponse {
    data: RatingChange[];
    error?: {};
}

export interface GetLastUpdatedApiResponse {
    data?: {
        date: string;
    };
    error?: {};
}

export function useApiClient() {
    const getRateableHistory = async (
        rateableType: RateableType,
        rateableId: string,
    ): Promise<RateableHistoryApiResponse> => {
        const response = await fetch(`/${rateableType}s/history/${rateableId}`);

        if (!response.ok) {
            return {
                data: [],
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

    return {
        getRateableHistory,
        getLastUpdated,
    };
}
