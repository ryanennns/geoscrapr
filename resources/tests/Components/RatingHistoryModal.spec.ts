import { describe, it, expect, vi, beforeEach } from "vitest";
import { flushPromises, mount } from "@vue/test-utils";
import { ref } from "vue";
import RatingHistoryModal from "../../js/Components/RatingHistoryModal.vue";
import { CountryCode } from "@/Composables/usePlayerUtils.ts";

const mockGetRateableHistory = vi.fn();
const { mockCreateRatingChart } = vi.hoisted(() => ({
    mockCreateRatingChart: vi.fn(() => ({
        destroy: vi.fn(),
    })),
}));

vi.mock("@/Composables/useApiClient.ts", () => ({
    useApiClient: () => ({
        getRateableHistory: mockGetRateableHistory,
        getRateable: vi.fn().mockResolvedValue({ data: null }),
        getMatchHistory: vi.fn().mockResolvedValue({ data: [] }),
    }),
}));

vi.mock("@/Composables/useBrowserUtils.ts", () => ({
    useBrowserUtils: () => ({
        isMobile: ref(false),
    }),
}));

vi.mock("@/Composables/useUrlParams.ts", () => ({
    useUrlParams: () => ({
        get: vi.fn().mockReturnValue(null),
        set: vi.fn(),
        clear: vi.fn(),
    }),
}));

vi.mock("@/Composables/useDarkMode", () => ({
    useDarkMode: () => ({
        isDark: ref(false),
    }),
}));

vi.mock("@/Composables/usePlayerUtils.ts", async (importOriginal) => {
    const actual =
        await importOriginal<
            typeof import("@/Composables/usePlayerUtils.ts")
        >();

    return {
        ...actual,
        usePlayerUtils: () => ({
            rateableToLeaderboardRows: vi.fn(),
            generateProfileUrl: vi.fn().mockReturnValue("#"),
        }),
    };
});

vi.mock("@/modalChartUtils.ts", () => ({
    createRatingChart: mockCreateRatingChart,
    getRatingHistoryChartData: vi.fn(() => ({
        labels: ["Mar 1"],
        data: [1000],
    })),
}));

const leaderboardRow = {
    id: "player_1",
    geoGuessrId: "gg_1",
    name: "Test Player",
    rating: 1000,
    moving_rating: 990,
    no_move_rating: 980,
    nmpz_rating: 970,
    countryCodes: [CountryCode.ca],
    isPlaceholder: false,
    type: "player" as const,
};

const ratingHistory = [
    {
        id: "history_overall",
        rating: 1000,
        created_at: "2026-03-20T12:00:00.000000Z",
        type: "overall" as const,
    },
    {
        id: "history_moving",
        rating: 990,
        created_at: "2026-03-20T12:00:00.000000Z",
        type: "moving" as const,
    },
    {
        id: "history_no_move",
        rating: 980,
        created_at: "2026-03-20T12:00:00.000000Z",
        type: "no_move" as const,
    },
    {
        id: "history_nmpz",
        rating: 970,
        created_at: "2026-03-20T12:00:00.000000Z",
        type: "nmpz" as const,
    },
];

const overallHistory = [ratingHistory[0]];
const movingHistory = [ratingHistory[1]];
const noMoveHistory = [ratingHistory[2]];
const nmpzHistory = [ratingHistory[3]];

describe("RatingHistoryModal.vue", () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mockGetRateableHistory.mockImplementation(
            async (_rateableType, _rateableId, ratingType) => {
                switch (ratingType) {
                    case "moving":
                        return { data: movingHistory };
                    case "no_move":
                        return { data: noMoveHistory };
                    case "nmpz":
                        return { data: nmpzHistory };
                    default:
                        return { data: overallHistory };
                }
            },
        );
        vi.spyOn(HTMLCanvasElement.prototype, "getContext").mockReturnValue({
            canvas: { height: 300 },
            createLinearGradient: vi.fn(() => ({
                addColorStop: vi.fn(),
            })),
        } as unknown as CanvasRenderingContext2D);
    });

    it("fetches typed histories on expand and plots them", async () => {
        const wrapper = mount(RatingHistoryModal, {
            props: {
                showModal: true,
                leaderboardRow,
                ratingHistory: overallHistory,
                loading: false,
            },
            global: {
                stubs: {
                    PlayerData: true,
                    ErrorMessage: true,
                    LoadingSpinner: true,
                    PlayerTeamSearch: true,
                    CloseButton: true,
                },
            },
        });

        await wrapper.get("button").trigger("click");
        await flushPromises();

        expect(mockGetRateableHistory).toHaveBeenCalledTimes(3);
        expect(mockGetRateableHistory).toHaveBeenNthCalledWith(
            1,
            "player",
            "player_1",
            "moving",
        );
        expect(mockGetRateableHistory).toHaveBeenNthCalledWith(
            2,
            "player",
            "player_1",
            "no_move",
        );
        expect(mockGetRateableHistory).toHaveBeenNthCalledWith(
            3,
            "player",
            "player_1",
            "nmpz",
        );
        expect(mockCreateRatingChart).toHaveBeenLastCalledWith(
            expect.objectContaining({
                datasets: expect.arrayContaining([
                    expect.objectContaining({
                        label: "Overall",
                        borderColor: "rgba(37, 99, 235, 0.9)",
                    }),
                    expect.objectContaining({
                        label: "Moving",
                        borderColor: "rgba(21, 128, 61, 0.9)",
                    }),
                    expect.objectContaining({
                        label: "No Move",
                        borderColor: "rgba(180, 83, 9, 0.9)",
                    }),
                    expect.objectContaining({
                        label: "NMPZ",
                        borderColor: "rgba(126, 34, 206, 0.9)",
                    }),
                ]),
            }),
        );
    });
});
