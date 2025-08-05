<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Http\Resources\TeamResource;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GetRateable extends Controller
{
    public function __invoke(Request $request)
    {
        $id = $request->input('id');
        $player = Player::query()->find($id);
        $team = Team::query()->find($id);

        if (!$player && !$team) {
            return response()->json([
                'error' => "Failed to find user or team for ID $id"
            ], Response::HTTP_NOT_FOUND);
        }

        $data = $player !== null ? new PlayerResource($player) : new TeamResource($team);

        return response()->json([
            'data' => $data,
        ]);
    }
}
