<?php

namespace Tests\Feature\Controllers;

use App\Models\Player;
use App\Models\RatingChange;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
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

        $response = $this->get("players/$player->id/history");
        $response->assertOk();

        $json = $response->json();

        $this->assertContains(
            $oldestRatingChange->toArray()['id'],
            collect($json['data'])->map(fn($rc) => $rc['id'])->toArray()
        );

        $this->assertCount(3, Arr::get($response->json(), 'data'));
    }

    public function test_it_returns_all_when_no_type_given()
    {
        Carbon::setTestNow(now());

        $player = Player::factory()->create();

        $player->ratingChanges()->create(
            RatingChange::factory()->raw(['created_at' => Carbon::now()->subWeek(), 'type' => RatingChange::TYPE_MOVING])
        );
        $player->ratingChanges()->create(
            RatingChange::factory()->raw(['created_at' => Carbon::now()->subWeek(), 'type' => RatingChange::TYPE_NO_MOVE])
        );
        $player->ratingChanges()->create(
            RatingChange::factory()->raw(['created_at' => Carbon::now()->subWeek(), 'type' => RatingChange::TYPE_NMPZ])
        );

        $player->ratingChanges()->create(
            RatingChange::factory()->raw(['created_at' => Carbon::now()->subDays(15)])
        );

        $response = $this->get("players/$player->id/history");
        $response->assertOk();

        $this->assertCount(5, Arr::get($response->json(), 'data'));
    }

    public function test_it_scopes_by_type()
    {
        Carbon::setTestNow(now());

        $player = Player::factory()->create();

        $player->ratingChanges()->create(
            RatingChange::factory()->raw(['created_at' => Carbon::now()->subWeek(), 'type' => RatingChange::TYPE_MOVING])
        );

        $player->ratingChanges()->create(
            RatingChange::factory()->raw(['created_at' => Carbon::now()->subDays(15)])
        );

        $response = $this->get("players/$player->id/history?type=moving");
        $response->assertOk();

        $this->assertCount(1, Arr::get($response->json(), 'data'));
    }
}
