<?php

namespace Tests\Feature\Controllers;

use App\Models\Player;
use App\Models\RatingChange;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetTeamRatingChangesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_two_weeks_plus_one_rating_change(): void
    {
        Carbon::setTestNow(now());

        $a = Player::factory()->create();
        $b = Player::factory()->create();
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

        $this->assertContains(
            $oldestRatingChange->toArray()['id'],
            collect($json['data'])->map(fn($rc) => $rc['id'])->toArray()
        );
    }
}
