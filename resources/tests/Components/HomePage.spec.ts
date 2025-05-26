import { describe, it, expect, vi, beforeEach } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { Chart } from "chart.js";
import HomePage from "@pages/HomePage.vue";

vi.mock("@composables/useRatingChart", () => ({
    useRatingChart: () => ({
        renderRangeChart: vi.fn(),
        renderPercentileChart: vi.fn(),
    }),
}));

vi.mock("@composables/useApiClient", () => ({
    useApiClient: () => ({
        getRateableHistory: vi.fn().mockResolvedValue({ data: [] }),
        getLastUpdated: vi.fn().mockResolvedValue({data: {date: new Date()}})
    }),
}));

const makeSnapshot = (date: string) => ({
    date,
    buckets: {},
    n: 1234,
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

    it.only("mounts successfully", () => {
        const wrapper = mountComponent();
        expect(wrapper.text()).toContain("GeoGuessr Ranking Distributions");
    });

    it("renders range chart on mount", async () => {
        const { renderRangeChart } = await import("@composables/useRatingChart").then(m => m.useRatingChart());
        mountComponent();
        expect(renderRangeChart).toHaveBeenCalledTimes(2); // solo and team
    });

    it("renders percentile chart when toggled", async () => {
        const wrapper = mountComponent();
        const { renderPercentileChart } = await import("@composables/useRatingChart").then(m => m.useRatingChart());

        // Set the toggle
        await wrapper.vm.$nextTick();
        wrapper.vm.selectedGraphType = "percentile";
        await flushPromises();

        expect(renderPercentileChart).toHaveBeenCalledTimes(2);
    });

    it("fetches player history when modal opens", async () => {
        const wrapper = mountComponent();
        const { getRateableHistory } = await import("@composables/useApiClient").then(m => m.useApiClient());

        const dummyRow = {
            id: "player_1",
            name: "Test Player",
            type: "player",
        };

        await wrapper.vm.onPlayerTeamClick({ rateable: dummyRow });
        await flushPromises();

        expect(getRateableHistory).toHaveBeenCalledWith("player", "player_1");
        expect(wrapper.vm.playerRatingHistory.length).toBe(0); // mocked response
    });

    it("displays chart sample size badges", () => {
        const wrapper = mountComponent();
        expect(wrapper.html()).toContain("n = 1,234");
    });

    it("reacts to resize events and updates charts", async () => {
        const wrapper = mountComponent();
        const updateChartsSpy = vi.spyOn(wrapper.vm, "updateCharts");

        window.dispatchEvent(new Event("resize"));
        await new Promise((r) => setTimeout(r, 300));

        expect(updateChartsSpy).toHaveBeenCalled();
    });

    it("destroys charts on unmount", () => {
        const wrapper = mountComponent();

        const soloDestroySpy = vi.fn();
        const teamDestroySpy = vi.fn();
        wrapper.vm.soloChartInstance = { destroy: soloDestroySpy } as unknown as Chart;
        wrapper.vm.teamChartInstance = { destroy: teamDestroySpy } as unknown as Chart;

        wrapper.unmount();
        expect(soloDestroySpy).toHaveBeenCalled();
        expect(teamDestroySpy).toHaveBeenCalled();
    });
});
