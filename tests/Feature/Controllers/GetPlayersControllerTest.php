<?php

namespace Tests\Feature\Controllers;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class GetPlayersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_players()
    {
        $this->withoutExceptionHandling();

        $player = Player::factory()->create();

        $response = $this->get('players');

        $response->assertSuccessful();

        $response->assertJsonFragment(['data' => [
            [
                'id'             => $player->id,
                'user_id'        => $player->user_id,
                'name'           => $player->name,
                'rating'         => $player->rating,
                'moving_rating'  => null,
                'no_move_rating' => null,
                'nmpz_rating'    => null,
                'country_code'   => $player->country_code,
            ]
        ]]);
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

    public static function provideRatingOptions(): array
    {
        return [
            'moving'  => ['moving'],
            'no_move' => ['no_move'],
            'nmpz'    => ['nmpz'],
        ];
    }
}
