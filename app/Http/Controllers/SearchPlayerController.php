<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchPlayerController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $query = $request->input('q');

        return PlayerResource::collection(
            Player::query()
                ->whereNotNull('rating')
                ->where('name', 'like', "%$query")
                ->orWhere('user_id', 'like', "%$query%")
                ->orderBy('rating', 'desc')
                ->limit(10)
                ->get()
        );
    }
}
