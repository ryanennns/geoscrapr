<?php

namespace App\Http\Controllers;

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

        $countries = Arr::get($validated, 'country', []);
        $order = Arr::get($validated, 'order');
        $active = Arr::get($validated, 'active');
        $gameType = Arr::get($validated, 'game_type');
        $page = Arr::get($validated, 'page', 1);

        $query = Player::query();

        if (!empty($countries)) {
            $countries = is_array($countries) ? $countries : [$countries];
            if (count($countries) > 0) {
                $query->whereIn('country_code', $countries);
            }
        }

        if ($active) {
            $query->whereHas('ratingChanges', function ($q) {
                $q->where('created_at', '>=', Carbon::now()->subWeek());
            });
        }

        if ($gameType !== null) {
            $column = $gameType . '_rating';
            $query->whereNotNull($column);
            $query->orderBy($column, $order ?? 'desc');
        }

        if ($gameType === null) {
            $query->whereNotNull('rating');
            $query->orderBy('rating', $order ?? 'desc');
        }

        return PlayerResource::collection(
            $query->forPage($page, 10)->limit(10)->get()
        );
    }
}
