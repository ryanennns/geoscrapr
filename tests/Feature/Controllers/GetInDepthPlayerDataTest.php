<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class GetInDepthPlayerDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fetches_data_from_geoguessr_api()
    {
        $uuid = Str::uuid();
        $gamesPlayed = 5076;
        $roundsPlayed = 25380;
        $maxGameScore = [
            "amount"     => "25000",
            "unit"       => "points",
            "percentage" => 100
        ];
        $averageGameScore = [
            "amount"     => "14262",
            "unit"       => "points",
            "percentage" => 57.048
        ];
        $maxRoundScore = [
            "amount"     => "5000",
            "unit"       => "points",
            "percentage" => 100
        ];
        $averageDistance = [
            "meters" => [
                "amount" => "1278.4",
                "unit"   => "km"
            ],
            "miles"  => [
                "amount" => "794.4",
                "unit"   => "miles"
            ]
        ];
        $averageTime = "60.134003 s";
        $timedOutGuesses = 71;
        $division = "Master I";
        Http::fake([
            'api/v3/users/*'              => Http::response([
                "gamesPlayed"                 => $gamesPlayed,
                "roundsPlayed"                => $roundsPlayed,
                "maxGameScore"                => $maxGameScore,
                "averageGameScore"            => $averageGameScore,
                "maxRoundScore"               => $maxRoundScore,
                "streakGamesPlayed"           => 61,
                "closestDistance"             => [
                    "meters" => [
                        "amount" => "0.5",
                        "unit"   => "m"
                    ],
                    "miles"  => [
                        "amount" => "0.6",
                        "unit"   => "yd"
                    ]
                ],
                "averageDistance"             => $averageDistance,
                "averageTime"                 => $averageTime,
                "timedOutGuesses"             => $timedOutGuesses,
                "battleRoyaleStats"           => [
                    [
                        "key"   => "Countries",
                        "value" => [
                            "gamesPlayed"     => 0,
                            "wins"            => 0,
                            "averagePosition" => 0
                        ]
                    ],
                    [
                        "key"   => "Distance",
                        "value" => [
                            "gamesPlayed"     => 0,
                            "wins"            => 0,
                            "averagePosition" => 0
                        ]
                    ]
                ],
                "dailyChallengeStreak"        => 1,
                "dailyChallengeCurrentStreak" => 0,
                "dailyChallengesRolling7Days" => [],
                "dailyChallengeMedal"         => 0,
                "streakMedals"                => [
                    [
                        "key"   => "CountryStreak",
                        "value" => 5
                    ]
                ],
                "streakRecords"               => [
                    [
                        "key"   => "CountryStreak",
                        "value" => [
                            "maxStreak"     => 5,
                            "maxStreakDate" => "2025-02-22T17:02:54.7330000Z"
                        ]
                    ]
                ]
            ]),
            'api/v4/ranked-system/best/*' => Http::response([
                "divisionNumber"   => 2,
                "divisionPosition" => 12,
                "divisionName"     => $division,
                "divisionTier"     => "Master"
            ]),
        ]);

        $response = $this->get("/players/$uuid/stats");

        $response->assertJson([
            'data' => [
                'gamesPlayed'      => $gamesPlayed,
                'roundsPlayed'     => $roundsPlayed,
                'maxGameScore'     => $maxGameScore,
                'averageGameScore' => $averageGameScore,
                'maxRoundScore'    => $maxRoundScore,
                'averageDistance'  => $averageDistance,
                'averageTime'      => $averageTime,
                'timedOutGuesses'  => $timedOutGuesses,
                'division'         => $division,
            ]
        ]);
    }
}
