<?php

namespace Tests\Feature\Controllers;

use App\GeoGuessrHttp;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GetMatchHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_gets_match_history(): void
    {
        $player = Player::factory()->create(["user_id" => "67771af50d5dc53559da3c7d"]);

        $data = [
            "userId"  => "67771af50d5dc53559da3c7d",
            "entries" => [
                [
                    "gameId"  => "a6e869a9-0a8b-4b4a-be81-41724426dccb",
                    "players" => [
                        [
                            "id"          => "67771af50d5dc53559da3c7d",
                            "nick"        => "Ave Cesaria",
                            "pin"         => [
                                "url"       => "pin/a61b51e62ecbac7c316e117f5ef467ac.png",
                                "anchor"    => "center-center",
                                "isDefault" => false,
                            ],
                            "fullBodyPin" => "pin/af06b9a9ce1d7495c27038c50e8a3d1d.png",
                            "countryCode" => "ki",
                            "tier"        => 80,
                        ],
                        [
                            "id"          => "5d6269cee9473f623039ce66",
                            "nick"        => "kiribatifriedlamb",
                            "pin"         => [
                                "url"       => "pin/e111e066a575e72a2b4ce54779bae256.png",
                                "anchor"    => "center-center",
                                "isDefault" => false,
                            ],
                            "fullBodyPin" => "pin/d3b4278d8ac2af25456724f2af5198c7.png",
                            "countryCode" => "se",
                            "tier"        => 190,
                        ],
                    ],
                    "duel"    => [
                        "gameId"   => "a6e869a9-0a8b-4b4a-be81-41724426dccb",
                        "teams"    => [
                            [
                                "players" => [
                                    [
                                        "playerId"           => "67771af50d5dc53559da3c7d",
                                        "rankedSystemRating" => 2325,
                                    ],
                                ],
                            ],
                            [
                                "players" => [
                                    [
                                        "playerId"           => "5d6269cee9473f623039ce66",
                                        "rankedSystemRating" => 2216,
                                    ],
                                ],
                            ],
                        ],
                        "gameMode" => "NoMoveDuels",
                        "status"   => 2,
                        "winnerId" => "67771af50d5dc53559da3c7d",
                        "rounds"   => [
                            [
                                "startTime" => "2025-09-23T14:16:49.4010000+00:00",
                            ],
                        ],
                    ],
                ],
                [
                    "gameId"  => "8d8ebbb7-3be4-4d76-8f07-061226fb25b2",
                    "players" => [
                        [
                            "id"          => "6771129d6d1359f9c1a2f187",
                            "nick"        => "Clementine",
                            "pin"         => [
                                "url"       => "pin/c53120d5f3556b90a3470b28dea3a790.png",
                                "anchor"    => "center-center",
                                "isDefault" => false,
                            ],
                            "fullBodyPin" => "pin/10f25aa49e136f6b2e5236dfb17bc8b0.png",
                            "countryCode" => "pl",
                            "tier"        => 60,
                        ],
                        [
                            "id"          => "67771af50d5dc53559da3c7d",
                            "nick"        => "Ave Cesaria",
                            "pin"         => [
                                "url"       => "pin/a61b51e62ecbac7c316e117f5ef467ac.png",
                                "anchor"    => "center-center",
                                "isDefault" => false,
                            ],
                            "fullBodyPin" => "pin/af06b9a9ce1d7495c27038c50e8a3d1d.png",
                            "countryCode" => "ki",
                            "tier"        => 80,
                        ],
                    ],
                    "duel"    => [
                        "gameId"   => "8d8ebbb7-3be4-4d76-8f07-061226fb25b2",
                        "teams"    => [
                            [
                                "players" => [
                                    [
                                        "playerId"           => "6771129d6d1359f9c1a2f187",
                                        "rankedSystemRating" => 2130,
                                    ],
                                ],
                            ],
                            [
                                "players" => [
                                    [
                                        "playerId"           => "67771af50d5dc53559da3c7d",
                                        "rankedSystemRating" => 2317,
                                    ],
                                ],
                            ],
                        ],
                        "gameMode" => "NoMoveDuels",
                        "status"   => 2,
                        "winnerId" => "67771af50d5dc53559da3c7d",
                        "rounds"   => [
                            [
                                "startTime" => "2025-09-19T14:54:51.1660000+00:00",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        Http::fake([
            GeoGuessrHttp::BASE_URL . 'api/v4/game-history/67771af50d5dc53559da3c7d' => Http::response($data)
        ]);

        $response = $this->get('players/' . $player->user_id . '/matches')
            ->assertStatus(200);

        $this->assertEquals($response->json(), $data);
    }
}
