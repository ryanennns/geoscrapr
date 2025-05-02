<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Http\Resources\TeamResource;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResource
    {
        $query = $request->input('q');

        $players = Player::query()
            ->where('name', 'like', "%$query%")
            ->orWhere('user_id', 'like', "%$query%")
            ->whereNotNull('rating')
            ->orderBy('rating', 'desc')
            ->limit(5)
            ->get();

        $teams = Team::query()
            ->where('name', 'like', "%$query%")
            ->orWhere('id', 'like', "%$query%")
            ->orderBy('rating', 'desc')
            ->limit(5)
            ->get();

        return JsonResource::collection([
            'players' => PlayerResource::collection($players),
            'teams' => TeamResource::collection($teams),
        ]);
    }
}
