<?php

namespace Feature\Controllers;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetPlayersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_players()
    {
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
}
