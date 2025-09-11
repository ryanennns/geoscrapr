<?php

namespace Tests\Feature\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\TestCase;

class GetRateableTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_player_if_exists()
    {
        $player = Player::factory()->create();

        $response = $this->get(route('rateables', ['id' => $player->getKey()]));
        $response->assertOk();

        $this->assertEquals($response->json(), [
            'data' => (new PlayerResource($player))->toArray(request()),
        ]);
    }

    public function test_it_returns_team_if_exists()
    {
        $playerOne = Player::factory()->create();
        $playerTwo = Player::factory()->create();
        $team = Team::factory()->create([
            'player_a' => $playerOne->user_id,
            'player_b' => $playerTwo->user_id,
        ]);

        $response = $this->get(route('rateables', ['id' => $team->getKey()]));
        $response->assertOk();

        $this->assertEquals(Arr::get($response->json(), 'data.id'), $team->getKey());
    }

    public function test_it_returns_not_found_if_no_player_or_team()
    {
        $response = $this->get(route('rateables', ['id' => Str::uuid()]));
        $response->assertNotFound();
    }
}
