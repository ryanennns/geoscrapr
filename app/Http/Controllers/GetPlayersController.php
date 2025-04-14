<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

class GetPlayersController extends Controller
{
    public function __invoke(Request $request): array
    {
        $validate = $request->validate([
            'country' => 'string',
        ]);

        $country = $validate['country'];

        return Player::query()
            ->where('country_code', $country)
            ->orderBy('rating', 'desc') // todo modularize this
            ->limit(10)
            ->get()
            ->toArray();
    }
}
