<?php

namespace App\Http\Controllers;

use App\Http\Enums\SortOrder;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class GetTeamsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $order = Arr::get(
            $request->validate(
                ['order' => Rule::enum(SortOrder::class)]
            ),
            'order'
        );

        $query = Team::query();

        if ($order) {
            $query->orderBy('rating', $order);
        }

        return TeamResource::collection(
            $query->with(['playerA', 'playerB'])
                ->limit(10)
                ->get()
        );
    }
}
