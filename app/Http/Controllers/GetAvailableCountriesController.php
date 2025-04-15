<?php

namespace App\Http\Controllers;

use App\Models\Player;

class GetAvailableCountriesController extends Controller
{
    public function __invoke(): array
    {
        return Player::query()
            ->select('country_code')
            ->distinct()
            ->pluck('country_code')
            ->toArray();
    }
}
