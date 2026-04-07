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
            'user_id' => Str::uuid()->toString(),
            'rating'  => 1000,
        ]);

        $this->assertDatabaseCount('rating_changes', 1);

        Http::fake([
            GeoGuessrHttp::BASE_URL . UpdatePlayerRatings::ENDPOINT . '*' => Http::sequence()
                ->push([
                    [
                        'userId'      => $player->user_id,
                        'nick'        => 'asdf',
                        'countryCode' => 'ca',
                        'rating'      => 500,
                    ]
                ], 200)
                ->push([], 200),
        ]);

        UpdatePlayerRatings::dispatch();

        $player->refresh();

        $this->assertEquals(500, $player->rating);

        $this->assertDatabaseCount('rating_changes', 2);
    }
}
