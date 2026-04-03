<?php

namespace Tests\Feature\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class GetTeamsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_returns_teams()
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        $team = Team::factory()->create([
            'player_a' => $playerA->user_id,
            'player_b' => $playerB->user_id,
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(collect([$team->load(['playerA', 'playerB'])]));

        $response = $this->getJson('teams');
        $response->assertSuccessful();
    }

    public function test_it_uses_cached_teams_when_available()
    {
        Carbon::setTestNow('2026-04-03 12:00:00');

        $playerA = Player::factory()->make([
            'id' => '00000000-0000-0000-0000-000000000001',
            'user_id' => 'cached-player-a',
            'name' => 'Cached Player A',
            'country_code' => 'us',
        ]);
        $playerB = Player::factory()->make([
            'id' => '00000000-0000-0000-0000-000000000002',
            'user_id' => 'cached-player-b',
            'name' => 'Cached Player B',
            'country_code' => 'ca',
        ]);
        $team = Team::factory()->make([
            'id' => '00000000-0000-0000-0000-000000000003',
            'team_id' => 'cached-team',
            'name' => 'Cached Team',
            'rating' => 2222,
            'player_a' => $playerA->user_id,
            'player_b' => $playerB->user_id,
        ]);
        $team->setRelation('playerA', $playerA);
        $team->setRelation('playerB', $playerB);

        Cache::shouldReceive('remember')
            ->once()
            ->with(
                'teams.index:active=0&date=2026-04-03&order=desc&page=1',
                Mockery::on(fn ($ttl) => $ttl instanceof \DateTimeInterface && $ttl->format('Y-m-d H:i:s') === '2026-04-04 12:00:00'),
                Mockery::type(\Closure::class),
            )
            ->andReturn(collect([$team]));

        $response = $this->getJson('teams');

        $response->assertSuccessful();
        $response->assertJsonPath('data.0.id', '00000000-0000-0000-0000-000000000003');
        $response->assertJsonPath('data.0.team_id', 'cached-team');
        $response->assertJsonPath('data.0.name', 'Cached Team');
        $response->assertJsonPath('data.0.rating', 2222);
    }

    public function test_it_builds_unique_cache_keys_from_request_params()
    {
        Carbon::setTestNow('2026-04-03 12:00:00');

        Cache::shouldReceive('remember')
            ->once()
            ->with(
                'teams.index:active=1&date=2026-04-03&order=asc&page=2',
                Mockery::on(fn ($ttl) => $ttl instanceof \DateTimeInterface && $ttl->format('Y-m-d H:i:s') === '2026-04-04 12:00:00'),
                Mockery::type(\Closure::class),
            )
            ->andReturn(collect());

        $response = $this->getJson('teams?active=1&order=asc&page=2');

        $response->assertSuccessful();
        $response->assertJsonCount(0, 'data');
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

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(collect([
                $team2->load(['playerA', 'playerB']),
                $team1->load(['playerA', 'playerB']),
            ]));

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

        $teams = Team::query()->with(['playerA', 'playerB'])->skip(10)->take(5)->get();

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($teams);

        $response = $this->getJson('teams?page=2');
        $response->assertSuccessful();
        $response->assertJsonCount(5, 'data');
    }
}
