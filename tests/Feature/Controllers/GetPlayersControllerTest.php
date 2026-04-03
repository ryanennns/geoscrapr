<?php

namespace Tests\Feature\Controllers;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class GetPlayersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_players()
    {
        $this->withoutExceptionHandling();

        $player = Player::factory()->create();

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(collect([$player]));

        $response = $this->get('players');

        $response->assertSuccessful();

        $response->assertJson(
            fn($json) => $json
                ->has('data.0',
                    fn($json) => $json->where('id', $player->id)
                        ->where('user_id', $player->user_id)
                        ->where('name', $player->name)
                        ->where('rating', $player->rating)
                        ->where('moving_rating', null)
                        ->where('no_move_rating', null)
                        ->where('nmpz_rating', null)
                        ->where('country_code', $player->country_code)
                        ->has('rank')        // only check the key exists
                        ->has('percentile')  // only check the key exists
                )
        );
    }

    public function test_it_uses_cached_players_when_available()
    {
        $cachedPlayer = Player::factory()->make([
            'id' => 999,
            'user_id' => 'cached-user',
            'name' => 'Cached Player',
            'rating' => 2123,
            'country_code' => 'US',
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->with(
                'players.index:active=0&game_type=rating&order=desc&page=1',
                Mockery::type(\DateTimeInterface::class),
                Mockery::type(\Closure::class),
            )
            ->andReturn(collect([$cachedPlayer]));

        $response = $this->getJson('players');

        $response->assertSuccessful();
        $response->assertJsonPath('data.0.id', 999);
        $response->assertJsonPath('data.0.user_id', 'cached-user');
        $response->assertJsonPath('data.0.name', 'Cached Player');
        $response->assertJsonPath('data.0.rating', 2123);
    }

    public function test_it_builds_unique_cache_keys_from_request_params()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with(
                'players.index:active=1&country%5B0%5D=ca&country%5B1%5D=us&game_type=moving&order=asc&page=2',
                Mockery::type(\DateTimeInterface::class),
                Mockery::type(\Closure::class),
            )
            ->andReturn(collect());

        $response = $this->getJson('players?active=1&country[]=us&country[]=ca&game_type=moving&order=asc&page=2');

        $response->assertSuccessful();
        $response->assertJsonCount(0, 'data');
    }

    /**
     * @dataProvider provideValidationCases
     */
    public function test_it_returns_unprocessable_if_validation_fails($key, $value)
    {
        $this->getJson('players?' . $key . '=' . $value)->assertStatus(422);
    }

    public static function provideValidationCases(): array
    {
        return [
            'invalid order'   => ['order', 'asdf'],
            'invalid country' => ['country', 'not a country'],
        ];
    }

    /**
     * @dataProvider provideRatingOptions
     */
    public function test_it_orders_by_ratings($gameType)
    {
        $playerOne = Player::factory()->create([
            'rating'              => 1000,
            $gameType . '_rating' => 1000,
        ]);

        $playerTwo = Player::factory()->create([
            'rating'              => 1000,
            $gameType . '_rating' => 2000,
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(collect([$playerTwo, $playerOne]));

        $response = $this->getJson('players?game_type=' . $gameType);

        $response->assertSuccessful();

        $response->assertJson([
            'data' => [
                [
                    'id'                  => $playerTwo->id,
                    'user_id'             => $playerTwo->user_id,
                    'name'                => $playerTwo->name,
                    'rating'              => $playerTwo->rating,
                    $gameType . '_rating' => $playerTwo->{$gameType . '_rating'},
                    'country_code'        => $playerTwo->country_code,
                ],
                [
                    'id'                  => $playerOne->id,
                    'user_id'             => $playerOne->user_id,
                    'name'                => $playerOne->name,
                    'rating'              => $playerOne->rating,
                    $gameType . '_rating' => $playerOne->{$gameType . '_rating'},
                    'country_code'        => $playerOne->country_code,],
            ]
        ]);
    }

    public function test_it_orders_by_rating_desc_by_default()
    {
        $players = Player::factory(10)->create();

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($players->sortByDesc('rating')->values());

        $response = $this->getJson('players');
        $response->assertSuccessful();

        collect(Arr::get($response->json(), 'data'))->each(
            function ($player, $index) use ($players) {
                $this->assertEquals(
                    Arr::get($players->sortByDesc('rating')
                        ->values(), $index . '.id'), Arr::get($player, 'id')
                );
            }
        );
    }

    public function test_it_paginates_response()
    {
        $players = Player::factory(20)->create();

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($players->sortByDesc('rating')->slice(10, 10)->values());

        $response = $this->getJson('players?page=2');
        $response->assertSuccessful();

        $data = Arr::get($response->json(), 'data');

        $this->assertCount(10, $data);
        $this->assertEquals(
            Arr::get($players->sortByDesc('rating')->values(), '10.id'),
            Arr::get($data, '0.id')
        );
    }

    public static function provideRatingOptions(): array
    {
        return [
            'moving'  => ['moving'],
            'no_move' => ['no_move'],
            'nmpz'    => ['nmpz'],
        ];
    }
}
