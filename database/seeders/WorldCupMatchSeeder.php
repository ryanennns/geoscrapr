<?php

namespace Database\Seeders;

use App\Models\WorldCupMatch;
use Illuminate\Database\Seeder;

class WorldCupMatchSeeder extends Seeder
{
    public function run(): void
    {
        WorldCupMatch::query()->create([
            'round'         => "Round 1",
            'player_one_id' => "57d301d409f2efcce834fc94",
            'player_two_id' => "601d17c1d565030001440b8d",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Round 2",
            'player_one_id' => "5de7a59044d2a42f78156b33",
            'player_two_id' => "603b1b0d5cdb1b0001bbf19e",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Round 3",
            'player_one_id' => "5bf491faaac55b998458ed9a",
            'player_two_id' => "5a973147afad0f2a68438531",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Round 4",
            'player_one_id' => "5e2e983722bbda85a40e9009",
            'player_two_id' => "57ebb537a52b273ab0162ed8",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Round 5",
            'player_one_id' => "5b51062a4010740f7cd91dd5",
            'player_two_id' => "5e5fcc1326bbda5284e824cf",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Round 6",
            'player_one_id' => "633a62ba560e8238dea97807",
            'player_two_id' => "5c03eed1b5b94ba700403005",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Round 7",
            'player_one_id' => "59d0b74bd8fe1d5b30651962",
            'player_two_id' => "635c171d190621fb60d8bb08",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Round 8",
            'player_one_id' => "55abc223ffb93d3658e4b76c",
            'player_two_id' => "5b4899f5b56fe41a1831bba4",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        // Quarter Finals
        WorldCupMatch::query()->create([
            'round'         => "Quarter Final 1",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Quarter Final 2",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Quarter Final 3",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Quarter Final 4",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        // Semi Finals
        WorldCupMatch::query()->create([
            'round'         => "Semi Final 1",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Semi Final 2",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        // Finals
        WorldCupMatch::query()->create([
            'round'         => "3rd Place",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        WorldCupMatch::query()->create([
            'round'         => "Grand Final",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

    }
}
