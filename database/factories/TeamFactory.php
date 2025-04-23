<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_id' => $this->faker->uuid(),
            'name'    => $this->faker->userName(),
            'rating'  => $this->faker->numberBetween(300, 2300),
        ];
    }
}
