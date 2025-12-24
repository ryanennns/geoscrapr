<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlayerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => $this->faker->shuffleString('abcdefghijklmnopqrstuvwxyz'),
            'name'           => $this->faker->userName(),
            'rating'         => $this->faker->numberBetween(200, 2300),
            'moving_rating'  => $this->faker->numberBetween(200, 2300),
            'no_move_rating' => $this->faker->numberBetween(200, 2300),
            'nmpz_rating'    => $this->faker->numberBetween(200, 2300),
            'country_code'   => strtolower($this->faker->countryCode()),
        ];
    }
}
