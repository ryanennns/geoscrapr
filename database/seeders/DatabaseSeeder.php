<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // $this->call(WorldCupMatchSeeder::class);

        Player::factory(100)->create();
        Artisan::call('snapshot:generate');
        Artisan::call('snapshot:percentile');
    }
}
