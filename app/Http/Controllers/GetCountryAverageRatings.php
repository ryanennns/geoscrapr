<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetCountryAverageRatings extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $countryCodes = Player::query()->select('country_code')->distinct()->get();

        $averages = [];

        foreach ($countryCodes as $countryCode) {
            $countryCode = $countryCode->country_code;

            if (Player::query()->where('country_code', $countryCode)->count() < 500) {
                continue;
            }

            $averages[$countryCode] = Player::query()->where('country_code', $countryCode)->avg('rating');
        }

        return response()->json([
            'data' => $averages,
        ]);
    }
}
