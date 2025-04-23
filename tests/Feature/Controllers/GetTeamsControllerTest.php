<?php

namespace Feature\Controllers;

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
        $response->assertJson([
            'data' => [
                [
                    'id'       => $team2->id,
                    'team_id'  => $team2->team_id,
                    'name'     => $team2->name,
                    'rating'   => $team2->rating,
                    'player_a' => $team2->player_a,
                    'player_b' => $team2->player_b,
                ],
                [
                    'id'       => $team1->id,
                    'team_id'  => $team1->team_id,
                    'name'     => $team1->name,
                    'rating'   => $team1->rating,
                    'player_a' => $team1->player_a,
                    'player_b' => $team1->player_b,
                ]
            ]
        ]);
    }
}
