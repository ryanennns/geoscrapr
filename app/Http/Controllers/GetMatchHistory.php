<?php

namespace App\Http\Controllers;

use App\GeoGuessrHttp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GetMatchHistory extends Controller
{
    public function __invoke(string $id, Request $request)
    {
        $response = Http::withHeaders([
            ...GeoGuessrHttp::HEADERS,
            'cookie' => GeoGuessrHttp::cookieString(),
        ])->get(GeoGuessrHttp::BASE_URL . "api/v4/game-history/$id");

        if (!$response->ok()) {
            response()->json(['error' => 'failed to fetch data'], 400);
        }

        return response()->json($response->json());
    }
}
