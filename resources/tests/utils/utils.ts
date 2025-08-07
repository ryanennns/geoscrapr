import type { Player } from "@/Types/core.ts";
import { v4 } from "uuid";
import type { CountryCode } from "@/Composables/usePlayerUtils.ts";

export const createPlayer = (overrides: Partial<Player> = {}): Player => {
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
