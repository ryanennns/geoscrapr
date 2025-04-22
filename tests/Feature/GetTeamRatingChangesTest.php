<?php

namespace Feature;

use App\Models\Player;
use App\Models\RatingChange;
use App\Models\Team;
use Carbon\Carbon;
use Tests\TestCase;

class GetTeamRatingChangesTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        Carbon::setTestNow(now());

        $a=Player::factory()->create();
        $b=Player::factory()->create();
        $team = Team::factory()->create([
            'player_a' => $a->user_id,
            'player_b' => $b->user_id,
        ]);

        $team->ratingChanges()->create(
            RatingChange::factory()->raw(['created_at' => Carbon::now()->subWeek()])
        );

        $team->ratingChanges()->create(
            RatingChange::factory()->raw(['created_at' => Carbon::now()->subDays(15)])
        );

        $oldestRatingChange = $team->ratingChanges()->orderBy('created_at', 'asc')->first();

        $response = $this->get("teams/history/$team->id");
        $response->assertOk();

        $json = $response->json();

        $this->assertContains($oldestRatingChange->toArray(), $json);
    }
}
