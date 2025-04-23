<?php

namespace App\Http\Controllers;

use App\Http\Enums\SortOrder;
use App\Http\Requests\GetPlayersRequest;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

class GetPlayersController extends Controller
{
    public function __invoke(GetPlayersRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $country = Arr::get($validated, 'country');
        $order = Arr::get($validated, 'order');

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

        return PlayerResource::collection(
            $query->limit(10)->get()
        );
    }
}
