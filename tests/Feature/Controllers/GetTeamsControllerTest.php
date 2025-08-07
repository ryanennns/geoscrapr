<?php

namespace Tests\Feature\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\RatingChange;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetTeamsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_teams()
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        $team = Team::factory()->create([
            'player_a' => $playerA->user_id,
            'player_b' => $playerB->user_id,
        ]);

        $response = $this->getJson('teams');
        $response->assertSuccessful();
    }

    public function test_it_returns_unprocessable_if_invalid_order()
    {
        $this->getJson('teams?order=not-an-order')->assertUnprocessable();
    }

    public function test_it_returns_teams_in_expected_order()
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        $team1 = Team::factory()->create([
            'rating'   => 1,
            'player_a' => $playerA->user_id,
            'player_b' => $playerB->user_id,
        ]);

        $team2 = Team::factory()->create([
            'rating'   => 2,
            'player_a' => $playerA->user_id,
            'player_b' => $playerB->user_id,
        ]);

        $response = $this->getJson('teams?order=desc');

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'data' => [
                [
                    'id'       => $team2->id,
                    'team_id'  => $team2->team_id,
                    'name'     => $team2->name,
                    'rating'   => $team2->rating,
                    'player_a' => (new PlayerResource($team2->playerA))->toArray(request()),
                    'player_b' => (new PlayerResource($team2->playerB))->toArray(request()),
                ],
                [
                    'id'       => $team1->id,
                    'team_id'  => $team1->team_id,
                    'name'     => $team1->name,
                    'rating'   => $team1->rating,
                    'player_a' => (new PlayerResource($team1->playerA))->toArray(request()),
                    'player_b' => (new PlayerResource($team1->playerB))->toArray(request()),
                ]
            ]
        ]);
    }

    public function test_it_paginates_response()
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        Team::factory()->count(15)->create([
            'player_a' => $playerA->user_id,
            'player_b' => $playerB->user_id,
        ]);

        $response = $this->getJson('teams?page=2');
        $response->assertSuccessful();
        $response->assertJsonCount(5, 'data');
    }
}
