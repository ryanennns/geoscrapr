<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

class SearchPlayerController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = $request->input('q');

        return Player::query()
            ->where('name', 'like', "%$query")
            ->orWhere('user_id', 'like', "%$query%")
            ->limit(10)
            ->get();
    }
}
