import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import PlayerLeaderboard from "@/Pages/PlayerLeaderboard.vue";
import type { Player, Rateable, RateableType } from "@/Types/core.ts";
import { v4 } from "uuid";
import type { CountryCode } from "@/Composables/usePlayerUtils.ts";
import { nextTick } from "vue";

const mockGetAvailableCountries = vi.fn();
const mockGetRateables = vi.fn();
vi.mock("@composables/useApiClient", () => ({
    useApiClient: () => {
        return {
            getAvailableCountries: mockGetAvailableCountries.mockResolvedValue({
                data: ["au", "nz", "ca"],
            }),
            getRateables: mockGetRateables.mockResolvedValue({
                data: [createPlayer()],
            }),
        };
    },
}));

const mountComponent = () => {
    return mount(PlayerLeaderboard, {
        props: {
            playersOrTeams: Array.from({ length: 5 }).map(() =>
                createRateable(),
            ),
        },
    });
};

describe("PlayerLeaderboard.vue", () => {
    let wrapper: ReturnType<typeof mountComponent>;

    beforeEach(async () => {
        vi.clearAllMocks();
        await flushPromises();

        wrapper = mountComponent();
    });

    it("renders title", () => {
        expect(wrapper.text()).toContain("Rating Leaderboard");
    });

    it("displays player names", async () => {
        const row = wrapper.get('[data-testid="row-1"]');
        const secondTd = row.findAll("td")[1];
        expect(secondTd.text()).toBe("some-player");
    });

    it("disables country dropdown when not in solo mode", async () => {
        const toggle = wrapper.findComponent('[data-testid="mode-toggle"]');
        await (toggle as any).vm.$emit("update:modelValue", "team");
        await nextTick();
        const countryDropdown = wrapper.get('[data-testid="country-dropdown"]');
        expect((countryDropdown.element as HTMLSelectElement).disabled).toBe(
            true,
        );
    });

    it("handles solo/team filter changes and updates leaderboard", async () => {
        const mockRateables = Array.from({ length: 10 }).map(() =>
            createRateable("team"),
        );
        mockGetRateables.mockResolvedValue({
            data: mockRateables,
        });

        expect(wrapper.text()).toContain("some-player");
        expect(wrapper.text()).not.toContain("some-team");

        const toggle = wrapper.findComponent('[data-testid="mode-toggle"]');
        expect(toggle).toBeDefined();

        await (toggle as any).vm.$emit("update:modelValue", "team");
        await nextTick();

        expect(mockGetRateables).toHaveBeenCalledTimes(1);
        expect(mockGetRateables).toHaveBeenCalledWith({
            playersOrTeams: "teams",
            active: "all",
            country: "all",
            order: "desc",
            gameType: "all",
        });

        expect((wrapper.vm as any).dataCache.all.all.desc.team.all).toEqual(
            mockRateables,
        );
        expect(wrapper.text()).toContain("some-team");
        expect(wrapper.text()).not.toContain("some-player");
    });

    it("changes order and updates leaderboard", async () => {
        const name = "low-ranked-dude";
        const mockRateables = Array.from({ length: 10 }).map((_, index) =>
            createPlayer({ name, rating: index * 100 }),
        );
        mockGetRateables.mockResolvedValue({
            data: mockRateables,
        });

        expect(wrapper.text()).toContain("some-player");

        const toggle = wrapper.findComponent('[data-testid="order-toggle"]');
        expect(toggle).toBeDefined();
        await (toggle as any).vm.$emit("update:modelValue", "asc");
        await nextTick();

        expect(mockGetRateables).toHaveBeenCalledTimes(1);
        expect(mockGetRateables).toHaveBeenCalledWith({
            playersOrTeams: "players",
            active: "all",
            country: "all",
            order: "asc",
            gameType: "all",
        });

        expect((wrapper.vm as any).dataCache.all.all.asc.solo.all).toEqual(
            mockRateables,
        );
        expect(wrapper.text()).not.toContain("some-player");
    });

    it("changes active filter and updates leaderboard", async () => {
        const mockRateables = Array.from({ length: 10 }).map(() =>
            createPlayer(),
        );
        mockGetRateables.mockResolvedValue({
            data: mockRateables,
        });

        expect(wrapper.text()).toContain("some-player");

        const toggle = wrapper.findComponent('[data-testid="active-toggle"]');
        expect(toggle).toBeDefined();
        await (toggle as any).vm.$emit("update:modelValue", "active");
        await nextTick();

        expect(mockGetRateables).toHaveBeenCalledTimes(1);
        expect(mockGetRateables).toHaveBeenCalledWith({
            playersOrTeams: "players",
            active: "active",
            country: "all",
            order: "desc",
            gameType: "all",
        });

        expect((wrapper.vm as any).dataCache.all.active.desc.solo.all).toEqual(
            mockRateables,
        );
    });

    it("changes game type filter and updates leaderboard", async () => {
        expect(wrapper.text()).toContain("some-player");

        const toggle = wrapper.findComponent(
            '[data-testid="game-type-toggle"]',
        );

        expect(toggle).toBeDefined();
        await (toggle as any).vm.$emit("update:modelValue", "moving");
        await nextTick();

        expect(mockGetRateables).toHaveBeenCalledTimes(1);
        expect(mockGetRateables).toHaveBeenCalledWith({
            playersOrTeams: "players",
            active: "all",
            country: "all",
            order: "desc",
            gameType: "moving",
        });
    });

    it("pulls from cache when existing data is available", async () => {
        const mockRateables = Array.from({ length: 10 }).map(() =>
            createRateable("team"),
        );
        mockGetRateables.mockResolvedValue({
            data: mockRateables,
        });

        const toggle = wrapper.findComponent('[data-testid="mode-toggle"]');
        expect(toggle).toBeDefined();

        await (toggle as any).vm.$emit("update:modelValue", "team");
        await nextTick();

        expect((wrapper.vm as any).dataCache.all.all.desc.team.all).toEqual(
            mockRateables,
        );

        await (toggle as any).vm.$emit("update:modelValue", "solo");
        await nextTick();

        expect(mockGetRateables).toHaveBeenCalledTimes(1);
    });

    it("handles country filter change", async () => {
        const dropdown = wrapper.findComponent({ name: "CountryDropdown" });
        await dropdown.vm.$emit("change", { country: "ca" });
        const comp = wrapper.vm as any;
        expect(comp.selectedCountry).toBe("ca");
    });

    it("emits playerClick when row is clicked", async () => {
        const row = wrapper.get('[data-testid="row-1"]');
        await row.trigger("click");
        expect(wrapper.emitted("playerClick")).toBeTruthy();
    });

    it("shows loading skeleton when loading is true", async () => {
        const comp = wrapper.vm as any;
        comp.loading = true;
        await nextTick();
        expect(
            wrapper
                .findComponent({ name: "LeaderboardLoadingSkeleton" })
                .isVisible(),
        ).toBe(true);
    });
});

const createPlayer = (overrides: Partial<Player> = {}): Player => {
    return {
        id: v4(),
        user_id: v4(),
        name: "some-player",
        rating: 1000,
        moving_rating: 990,
        no_move_rating: 985,
        nmpz_rating: 1001,
        country_code: "nz" as CountryCode,
        ...overrides,
    };
};

const createRateable = (type: RateableType = "player"): Rateable => {
    if (type === "player") {
        return createPlayer();
    }

    return {
        id: v4(),
        team_id: v4(),
        name: "some-team",
        rating: 1000,
        player_a: createPlayer(),
        player_b: createPlayer(),
    };
};
