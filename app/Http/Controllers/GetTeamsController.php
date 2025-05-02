<?php

namespace App\Http\Controllers;

use App\Http\Enums\SortOrder;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class GetTeamsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'order'  => Rule::enum(SortOrder::class),
            'active' => 'boolean|nullable',
        ]);

        $order = Arr::get($validated, 'order');
        $active = Arr::get($validated, 'active');

        $query = Team::query();

        if ($order) {
            $query->orderBy('rating', $order);
        }

        if ($active) {
            $query->where('updated_at', '>=', Carbon::now()->subWeek());
        }

        return TeamResource::collection(
            $query->with(['playerA', 'playerB'])
                ->limit(10)
                ->get()
        );
    }
}
