<?php

namespace Feature\Controllers;

use App\Models\Player;
use App\Models\RatingChange;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetPlayerRatingChangesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_two_weeks_plus_one_rating_change(): void
    {
        Carbon::setTestNow(now());

        $player = Player::factory()->create();

        $player->ratingChanges()->create(
            RatingChange::factory()->raw(['created_at' => Carbon::now()->subWeek()])
        );

        $player->ratingChanges()->create(
            RatingChange::factory()->raw(['created_at' => Carbon::now()->subDays(15)])
        );

        $oldestRatingChange = $player->ratingChanges()->orderBy('created_at', 'asc')->first();

        $response = $this->get("players/history/$player->id");
        $response->assertOk();

        $json = $response->json();

        $this->assertContains(
            $oldestRatingChange->toArray()['id'],
            collect($json['data'])->map(fn($rc) => $rc['id'])->toArray()
        );
    }
}
