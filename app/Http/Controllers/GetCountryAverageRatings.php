<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class GetCountryAverageRatings extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $gamemode = $request->input('gamemode');
        $ratingColumnName = $gamemode ? $gamemode . '_rating' : 'rating';

        $countryCodes = Player::query()->select('country_code')->distinct()->get();

        $averages = [];

        foreach ($countryCodes as $countryCode) {
            $countryCode = $countryCode->country_code;

            if (Player::query()->where('country_code', $countryCode)->count() < Config::get('geo.minimum_average_country_players')) {
                continue;
            }

            $averages[$countryCode] = Player::query()
                ->where('country_code', $countryCode)
                ->avg($ratingColumnName);
        }

        return response()->json([
            'data' => $averages,
        ]);
    }
}
