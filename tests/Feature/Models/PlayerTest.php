<?php

namespace Tests\Feature\Models;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('providePlayerSetup')]
    public function test_it_creates_rating_changes_on_creation($array, $expected): void
    {
        $player = Player::factory()->create($array);

        $this->assertCount($expected, $player->ratingChanges);
    }

    public static function providePlayerSetup(): array
    {
        return [
            [['rating' => 1], 1],
            [['rating' => 1, 'moving_rating' => 1], 2],
            [['rating' => 1, 'no_move_rating' => 1], 2],
            [['rating' => 1, 'nmpz_rating' => 1], 2],
            [
                [
                    'rating'         => 1,
                    'moving_rating'  => 1,
                    'no_move_rating' => 1,
                    'nmpz_rating'    => 1
                ],
                4
            ],
        ];
    }
}
