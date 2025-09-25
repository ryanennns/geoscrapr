<?php

namespace App\Http\Controllers;

use App\GeoGuessrHttp;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class GetMatchHistory extends Controller
{
    public function __invoke(string $id, Request $request)
    {
        $playerUserId = Player::query()
            ->select('user_id')
            ->where('id', $id)
            ->first()
            ->user_id;

        $response = Http::withHeaders([
            ...GeoGuessrHttp::HEADERS,
            'cookie' => GeoGuessrHttp::cookieString(),
        ])->get(GeoGuessrHttp::BASE_URL . "api/v4/game-history/$playerUserId");

        if (!$response->ok()) {
            response()->json(['error' => 'failed to fetch data'], 400);
        }

        $json = $response->json();

        $games = Arr::get($json, 'entries');

        $r = collect($games)->map(function ($game) use ($games) {
            $id = Arr::get($game, 'gameId');
            $winnerUserId = Arr::get($game, 'duel.winnerId');

            return [
                'id'         => $id,
                'winner'     => Player::query()->where('user_id', $winnerUserId)->select('id')->first()?->id,
                'started_at' => Carbon::parse(Arr::get($game, 'rounds.0.startTime')),
            ];
        });

        return response()->json($r);
    }
}
