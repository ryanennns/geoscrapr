<?php

namespace App\Http\Controllers;

use App\GeoGuessrHttp;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class GetInDepthPlayerData extends Controller
{
    public function __invoke(string $id, Request $request)
    {
        $statsResponse = Http::withHeaders([
            ...GeoGuessrHttp::HEADERS,
            "cookie" => GeoGuessrHttp::cookieString()
        ])->get(GeoGuessrHttp::BASE_URL . 'api/v3/users/' . $id . '/stats');

        $divisionResponse = Http::withHeaders([
            ...GeoGuessrHttp::HEADERS,
            "cookie" => GeoGuessrHttp::cookieString()
        ])->get(GeoGuessrHttp::BASE_URL . 'api/v4/ranked-system/best/' . $id);

        if ($divisionResponse->failed() || $statsResponse->failed()) {
            return response()->json(['error' => 'failed to fetch data'], 400);
        }

        $statsJson = $statsResponse->json();
        $divisionJson = $divisionResponse->json();
        return response()->json([
            'data' => [
                'gamesPlayed'                 => Arr::get($statsJson, 'gamesPlayed'),
                'roundsPlayed'                => Arr::get($statsJson, 'roundsPlayed'),
                'maxGameScore'                => Arr::get($statsJson, 'maxGameScore'),
                'averageGameScore'            => Arr::get($statsJson, 'averageGameScore'),
                'maxRoundScore'               => Arr::get($statsJson, 'maxRoundScore'),
                'streakGamesPlayed'           => Arr::get($statsJson, 'streakGamesPlayed'),
                'averageDistance'             => Arr::get($statsJson, 'averageDistance'),
                'averageTime'                 => Arr::get($statsJson, 'averageTime'),
                'timedOutGuesses'             => Arr::get($statsJson, 'timedOutGuesses'),
                'division'                    => Arr::get($divisionJson, 'divisionName'),
            ]
        ]);
    }
}
