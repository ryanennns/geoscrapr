<?php

namespace Tests\Jobs;

use App\GeoGuessrHttp;
use App\Jobs\UpdatePlayerRatings;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdatePlayerRatingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_rating_and_creates_rating_change()
    {
        $player = Player::factory()->create([
            'user_id'       => Str::uuid()->toString(),
            'nmpz_rating'   => 1000,
            'moving_rating' => 1000,
        ]);

        $this->assertDatabaseCount('rating_changes', 3);

        Http::fake([
            GeoGuessrHttp::BASE_URL . UpdatePlayerRatings::ENDPOINT . '*' => Http::sequence()
                ->push([[
                    'userId'      => $player->user_id,
                    'nick'        => 'asdf',
                    'countryCode' => 'ca',
                    'rating'      => 500,
                ]])
                ->push([])
                ->push([[
                    'userId'        => $player->user_id,
                    'nick'          => 'asdf',
                    'countryCode'   => 'ca',
                    'moving_rating' => 500,
                    'rating'        => 500
                ]])->push([])
        ]);

        UpdatePlayerRatings::dispatch();

        $player->refresh();

        $this->assertEquals(500, $player->moving_rating);

        $this->assertDatabaseCount('rating_changes', 5);
    }

    public function test_prod()
    {
        Http::fake([
            '*' => Http::response($array = [
                [
                    "position" => 1,
                    "rating" => 2473,
                    "userId" => "5ff2cfa24d5c0400011fc340",
                    "nick" => "stique",
                    "avatar" => "pin/ad77115d81f64eb429d83dcc2a1842bd.png",
                    "fullBodyPath" => "pin/9342338e2beac1d034bb941bada744f6.png",
                    "isVerified" => true,
                    "isDeleted" => false,
                    "flair" => 2,
                    "countryCode" => "nl",
                    "club" => [
                        "tag" => "TISM",
                        "clubId" => "bc9fd733-b468-41a7-8005-b496a715e487",
                        "level" => 10,
                    ],
                    "borderUrl" => "avatarasseticon/4b318acbf586389c79ea61edbd0607a3.webp",
                ],
                [
                    "position" => 2,
                    "rating" => 2441,
                    "userId" => "687a441ad565a7d063983eb8",
                    "nick" => "YRP & J.D. Vance shared alt",
                    "avatar" => "pin/8991e9b26a97227f41ac15c3d78d44bc.png",
                    "fullBodyPath" => "pin/25238002b8faadbd6a7424a398a5cb15.png",
                    "isVerified" => false,
                    "isDeleted" => false,
                    "flair" => 0,
                    "countryCode" => "jp",
                    "club" => [
                        "tag" => "IGBV",
                        "clubId" => "6e7c6ecc-af82-435f-8ae9-5c93cfafadc9",
                        "level" => 13,
                    ],
                    "borderUrl" => "avatarasseticon/73c2474d23005aca3d9b4cdd96bd81f1.webp",
                ],
                [
                    "position" => 3,
                    "rating" => 2438,
                    "userId" => "686169a2e52bd7ffd47bfe22",
                    "nick" => "게스마스터",
                    "avatar" => "pin/c103880290e5ab1d5f06c62f6d534deb.png",
                    "fullBodyPath" => "pin/ed5a54beb6426bc8a8913693d3b88c5e.png",
                    "isVerified" => false,
                    "isDeleted" => false,
                    "flair" => 0,
                    "countryCode" => "kr",
                    "club" => [
                        "tag" => "CUCK",
                        "clubId" => "f8eb7c14-cef3-45c6-b739-0cc84fbbea25",
                        "level" => 16,
                    ],
                    "borderUrl" => "avatarasseticon/4b318acbf586389c79ea61edbd0607a3.webp",
                ],
                [
                    "position" => 4,
                    "rating" => 2433,
                    "userId" => "62f0c9a9822a3f816d659d84",
                    "nick" => "2500 kitten",
                    "avatar" => "pin/309f8e5102ef68e082ff9e9cbf533754.png",
                    "fullBodyPath" => "pin/dc45be68ed170c404eb2fb488c6c4ed5.png",
                    "isVerified" => false,
                    "isDeleted" => false,
                    "flair" => 1,
                    "countryCode" => "pl",
                    "club" => [
                        "tag" => "TISM",
                        "clubId" => "bc9fd733-b468-41a7-8005-b496a715e487",
                        "level" => 10,
                    ],
                    "borderUrl" => "avatarasseticon/4b318acbf586389c79ea61edbd0607a3.webp",
                ],
                [
                    "position" => 5,
                    "rating" => 2417,
                    "userId" => "65361a91884b107b4fd0cef8",
                    "nick" => "YRP",
                    "avatar" => "pin/6be53e71a80c6fc188d3606f1310cfa9.png",
                    "fullBodyPath" => "pin/78b65f414c916cd85d33a755f2f3063b.png",
                    "isVerified" => false,
                    "isDeleted" => false,
                    "flair" => 0,
                    "countryCode" => "jp",
                    "club" => [
                        "tag" => "GANG",
                        "clubId" => "da5d17ea-0f90-4e4c-b0a8-e85948fb6723",
                        "level" => 18,
                    ],
                    "borderUrl" => "avatarasseticon/c75d76874460da4c7902664c68857d15.webp",
                ],
            ])
        ]);

        UpdatePlayerRatings::dispatch();
    }
}
