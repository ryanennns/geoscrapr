<?php

namespace App\Http\Controllers;

use App\Http\Enums\SortOrder;
use App\Http\Requests\GetPlayersRequest;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

class GetPlayersController extends Controller
{
    public function __invoke(GetPlayersRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $country = Arr::get($validated, 'country');
        $order = Arr::get($validated, 'order');
        $active = Arr::get($validated, 'active');

        $query = Player::query();

        if ($country !== null) {
            $query->where('country_code', $country);
        }

        if ($order !== null) {
            $query->orderBy('rating', $order);

            if ($order === 'asc') {
                $query->whereNotNull('rating');
            }
        }

        if ($active) {
            $query->where('updated_at', '>=', Carbon::now()->subWeek());
        }

        return PlayerResource::collection(
            $query->limit(10)->get()
        );
    }
}
