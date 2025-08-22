<?php

namespace Database\Seeders;

use App\Models\WorldCupMatch;
use Illuminate\Database\Seeder;

class WorldCupMatchSeeder extends Seeder
{
    public function run(): void
    {
        // Predeclare placeholders
        $thirdPlaceDecider = null;
        $grandFinals = null;
        $sf1 = null;
        $sf2 = null;
        $qf1 = null;
        $qf2 = null;
        $qf3 = null;
        $qf4 = null;

        // --- Round 1-8 ---
        $round1 = WorldCupMatch::query()->create([
            'round'         => "Round 1",
            'player_one_id' => "57d301d409f2efcce834fc94",
            'player_two_id' => "601d17c1d565030001440b8d",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        $round2 = WorldCupMatch::query()->create([
            'round'         => "Round 2",
            'player_one_id' => "5de7a59044d2a42f78156b33",
            'player_two_id' => "603b1b0d5cdb1b0001bbf19e",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        $round3 = WorldCupMatch::query()->create([
            'round'         => "Round 3",
            'player_one_id' => "5bf491faaac55b998458ed9a",
            'player_two_id' => "5a973147afad0f2a68438531",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        $round4 = WorldCupMatch::query()->create([
            'round'         => "Round 4",
            'player_one_id' => "5e2e983722bbda85a40e9009",
            'player_two_id' => "57ebb537a52b273ab0162ed8",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        $round5 = WorldCupMatch::query()->create([
            'round'         => "Round 5",
            'player_one_id' => "5b51062a4010740f7cd91dd5",
            'player_two_id' => "5e5fcc1326bbda5284e824cf",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        $round6 = WorldCupMatch::query()->create([
            'round'         => "Round 6",
            'player_one_id' => "633a62ba560e8238dea97807",
            'player_two_id' => "5c03eed1b5b94ba700403005",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        $round7 = WorldCupMatch::query()->create([
            'round'         => "Round 7",
            'player_one_id' => "59d0b74bd8fe1d5b30651962",
            'player_two_id' => "635c171d190621fb60d8bb08",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        $round8 = WorldCupMatch::query()->create([
            'round'         => "Round 8",
            'player_one_id' => "55abc223ffb93d3658e4b76c",
            'player_two_id' => "5b4899f5b56fe41a1831bba4",
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        // --- Quarter Finals ---
        $qf1 = WorldCupMatch::query()->create([
            'round'         => "Quarter Final 1",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
            'next_match_id' => null, // will patch later
        ]);

        $qf2 = WorldCupMatch::query()->create([
            'round'         => "Quarter Final 2",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
            'next_match_id' => null,
        ]);

        $qf3 = WorldCupMatch::query()->create([
            'round'         => "Quarter Final 3",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
            'next_match_id' => null,
        ]);

        $qf4 = WorldCupMatch::query()->create([
            'round'         => "Quarter Final 4",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
            'next_match_id' => null,
        ]);

        // Attach Round winners to QFs
        $round1->update(['next_match_id' => $qf1->getKey()]);
        $round2->update(['next_match_id' => $qf1->getKey()]);
        $round3->update(['next_match_id' => $qf2->getKey()]);
        $round4->update(['next_match_id' => $qf2->getKey()]);
        $round5->update(['next_match_id' => $qf3->getKey()]);
        $round6->update(['next_match_id' => $qf3->getKey()]);
        $round7->update(['next_match_id' => $qf4->getKey()]);
        $round8->update(['next_match_id' => $qf4->getKey()]);

        // --- Semi Finals ---
        $sf1 = WorldCupMatch::query()->create([
            'round'          => "Semi Final 1",
            'player_one_id'  => null,
            'player_two_id'  => null,
            'link'           => "https://twitch.tv/GeoGuessr",
            'next_match_id'  => null,
            'loser_match_id' => null,
        ]);

        $sf2 = WorldCupMatch::query()->create([
            'round'          => "Semi Final 2",
            'player_one_id'  => null,
            'player_two_id'  => null,
            'link'           => "https://twitch.tv/GeoGuessr",
            'next_match_id'  => null,
            'loser_match_id' => null,
        ]);

        $qf1->update(['next_match_id' => $sf1->getKey()]);
        $qf2->update(['next_match_id' => $sf1->getKey()]);
        $qf3->update(['next_match_id' => $sf2->getKey()]);
        $qf4->update(['next_match_id' => $sf2->getKey()]);

        // --- Finals ---
        $thirdPlaceDecider = WorldCupMatch::query()->create([
            'round'         => "3rd Place",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        $grandFinals = WorldCupMatch::query()->create([
            'round'         => "Grand Final",
            'player_one_id' => null,
            'player_two_id' => null,
            'link'          => "https://twitch.tv/GeoGuessr",
        ]);

        $sf1->update([
            'next_match_id'  => $grandFinals->getKey(),
            'loser_match_id' => $thirdPlaceDecider->getKey(),
        ]);

        $sf2->update([
            'next_match_id'  => $grandFinals->getKey(),
            'loser_match_id' => $thirdPlaceDecider->getKey(),
        ]);
    }
}
