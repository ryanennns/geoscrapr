<?php

namespace Feature\Controllers;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCountryAverageRatingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_country_average_ratings()
    {
        Player::factory()->count(500)->create(['country_code' => 'ca']);
        Player::factory()->count(500)->create(['country_code' => 'us']);
        Player::factory()->count(500)->create(['country_code' => 'de']);

        $response = $this->getJson('country-average-ratings');
        $response->assertOk();

        $response->assertJson([
            'data' => [
                'ca' => Player::query()->where('country_code', 'ca')->avg('rating'),
                'us' => Player::query()->where('country_code', 'us')->avg('rating'),
                'de' => Player::query()->where('country_code', 'de')->avg('rating'),
            ],
        ]);
    }
}
