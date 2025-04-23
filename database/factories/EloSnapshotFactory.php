<?php

namespace Database\Factories;

use App\Models\EloSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloSnapshotFactory extends Factory
{
    public function definition(): array
    {
        $buckets = [];
        $n = 0;
        for ($i = 0; $i <= 2300; $i += 100) {
            $key = "$i-" . ($i + 99);
            $num = $this->faker->numberBetween(0, 5000);

            $buckets[$key] = $num;
            $n = $n + $num;
        }

        return [
            'date'     => $this->faker->date(),
            'gamemode' => $this->faker->randomElement(EloSnapshot::GAMEMODES),
            'buckets'  => json_encode($buckets),
            'n'        => $n,
        ];
    }

}
