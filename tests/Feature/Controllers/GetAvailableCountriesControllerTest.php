<?php

namespace Tests\Feature\Controllers;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAvailableCountriesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_all_country_codes_relating_to_players(): void
    {
        Player::factory()->create(['country_code' => 'bg']);
        Player::factory()->create(['country_code' => 'ca']);
        Player::factory()->create(['country_code' => 'kz']);

        $response = $this->get('countries');

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'bg',
                'ca',
                'kz',
            ]
        ]);
    }
}
