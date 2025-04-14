<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class GetTeamsController extends Controller
{
    public function __invoke(Request $request): array
    {
        return Team::query()
            ->orderBy('rating', 'desc')
            ->with(['playerA', 'playerB'])
            ->limit(10)
            ->get()
            ->toArray();
    }
}
