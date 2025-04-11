<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class SearchPlayerController extends Controller
{
    public function __invoke(Request $request): Collection
    {
        $query = $request->input('q');

        return Player::query()
            ->whereNotNull('rating')
            ->where('name', 'like', "%$query")
            ->orWhere('user_id', 'like', "%$query%")
            ->orderBy('rating', 'desc')
            ->limit(10)
            ->get();
    }
}
