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
        const toggle = wrapper.findAllComponents({ name: "Toggle" })[0];
        await toggle.vm.$emit("update:modelValue", "team");
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
        expect(wrapper.text()).not.toContain("some-teamname");

        const toggle = wrapper.findAllComponents({ name: "Toggle" })[0];
        expect(toggle).toBeDefined();

        await toggle.vm.$emit("update:modelValue", "team");
        await nextTick();

        expect(mockGetRateables).toHaveBeenCalledTimes(1);
        expect(mockGetRateables).toHaveBeenCalledWith({
            playersOrTeams: "teams",
            active: "all",
            country: "all",
            order: "desc",
        });

        expect((wrapper.vm as any).dataCache.all.desc.team.all).toEqual(mockRateables);
        expect(wrapper.text()).toContain("some-teamname");
        expect(wrapper.text()).not.toContain("some-player");
    });

    it.todo("changes order and updates leaderboard", async () => {});

    it.todo("changes active filter and updates", async () => {});

    it.todo("changes game mode filter and updates leaderboard", async () => {});

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
        name: "some-teamname",
        rating: 1000,
        player_a: createPlayer(),
        player_b: createPlayer(),
    };
};
