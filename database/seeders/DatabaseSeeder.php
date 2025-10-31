<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\RatingChange;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $players = Player::factory(100)->create();
        $players->each(function (Player $player) {
            for ($i = 0; $i < 14; $i++) {
                $player->ratingChanges()->create(
                    RatingChange::factory()->raw([
                        'created_at' => now()->subDays($i),
                    ])
                );
            }
        });
        Artisan::call('snapshot:generate');
        Artisan::call('snapshot:percentile');
    }
}
