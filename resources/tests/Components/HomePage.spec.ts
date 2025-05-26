import { describe, it, expect, vi, beforeEach } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { Chart } from "chart.js";
import HomePage from "../../js/Pages/HomePage.vue";
import type {Snapshot} from "@/Types/core.ts";

const mockRenderRangeChart = vi.fn();
const mockRenderPercentileChart = vi.fn();

vi.mock("@composables/useRatingChart", () => ({
    useRatingChart: () => ({
        renderRangeChart: mockRenderRangeChart,
        renderPercentileChart: mockRenderPercentileChart,
    }),
}));

const mockGetRateableHistory = vi.fn();
vi.mock("@composables/useApiClient", () => ({
    useApiClient: () => ({
        getRateableHistory: mockGetRateableHistory.mockResolvedValue({ data: [] }),
        getLastUpdated: vi.fn().mockResolvedValue({data: {date: new Date()}})
    }),
}));

const makeSnapshot = (date: string): Snapshot => ({
    date,
    buckets: {},
    n: 1234,
    type: 'elo_range',
});

const mountComponent = (overrides = {}) => {
    return mount(HomePage, {
        props: {
            solo_snapshots: [makeSnapshot("2024-01-01")],
            team_snapshots: [makeSnapshot("2024-01-01")],
            solo_percentile_snapshots: [makeSnapshot("2024-01-01")],
            team_percentile_snapshots: [makeSnapshot("2024-01-01")],
            range_dates: ["2024-01-01"],
            percentile_dates: ["2024-01-01"],
            leaderboard: [],
            ...overrides,
        },
    });
};

describe("HomePage.vue", () => {
    beforeEach(() => {
        vi.clearAllMocks();

        vi.stubGlobal('fetch', vi.fn(() =>
            Promise.resolve({
                ok: true,
                json: () => Promise.resolve([]),
            }),
        ));

    });

    it("mounts successfully", () => {
        const wrapper = mountComponent();
        expect(wrapper.text()).toContain("GeoGuessr Ranking Distributions");
    });

    it("renders range chart on mount", async () => {
        mountComponent();
        expect(mockRenderRangeChart).toHaveBeenCalledTimes(2);
    });

    it("renders percentile chart when toggled", async () => {
        const wrapper = mountComponent();

        await wrapper.vm.$nextTick();
        (wrapper.vm as unknown as typeof HomePage).selectedGraphType = "percentile";
        await flushPromises();

        expect(mockRenderPercentileChart).toHaveBeenCalledTimes(2);
    });

    it("fetches player history when modal opens", async () => {
        const wrapper = mountComponent();

        const dummyRow = {
            id: "player_1",
            name: "Test Player",
            type: "player",
        };

        await ((wrapper.vm as any)).onPlayerTeamClick({ rateable: dummyRow });
        await flushPromises();

        expect(mockGetRateableHistory).toHaveBeenCalledWith("player", "player_1");
        expect((wrapper.vm as any).playerRatingHistory.length).toBe(0);
    });

    it("displays chart sample size badges", () => {
        const wrapper = mountComponent();
        expect(wrapper.html()).toContain("n = 1,234");
    });

    it.skip("reacts to resize events and updates charts", async () => {
        const wrapper = mountComponent();
        const updateChartsSpy = vi.spyOn((wrapper.vm as any), "updateCharts");

        window.dispatchEvent(new Event("resize"));
        await new Promise((r) => setTimeout(r, 300));

        expect(updateChartsSpy).toHaveBeenCalled();
    });

    it.skip("destroys charts on unmount", () => {
        const wrapper = mountComponent();

        const soloDestroySpy = vi.fn();
        const teamDestroySpy = vi.fn();
        (wrapper.vm as any).soloChartInstance = { destroy: soloDestroySpy } as unknown as Chart;
        (wrapper.vm as any).teamChartInstance = { destroy: teamDestroySpy } as unknown as Chart;

        wrapper.unmount();
        expect(soloDestroySpy).toHaveBeenCalled();
        expect(teamDestroySpy).toHaveBeenCalled();
    });
});
