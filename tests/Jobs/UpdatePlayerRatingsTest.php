<?php

namespace Tests\Jobs;

use App\GeoGuessrHttp;
use App\Jobs\UpdatePlayerRatings;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdatePlayerRatingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_rating_and_creates_rating_change()
    {
        $player = Player::factory()->create([
            'user_id'       => Str::uuid()->toString(),
            'nmpz_rating'   => 1000,
            'moving_rating' => 1000,
        ]);

        $this->assertDatabaseCount('rating_changes', 3);

        Http::fake([
            GeoGuessrHttp::BASE_URL . UpdatePlayerRatings::ENDPOINT . '*' => Http::sequence()
                ->push([[
                    'userId'      => $player->user_id,
                    'nick'        => 'asdf',
                    'countryCode' => 'ca',
                    'rating'      => 500,
                ]])
                ->push([])
                ->push([[
                    'userId'        => $player->user_id,
                    'nick'          => 'asdf',
                    'countryCode'   => 'ca',
                    'moving_rating' => 500,
                    'rating'        => 500
                ]])->push([])
        ]);

        UpdatePlayerRatings::dispatch();

        $player->refresh();

        $this->assertEquals(500, $player->moving_rating);

        $this->assertDatabaseCount('rating_changes', 5);
    }
}
