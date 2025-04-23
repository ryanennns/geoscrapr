<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GetPlayersController extends Controller
{
    public function __invoke(Request $request): array
    {
        $validate = $request->validate([
            'country' => 'string',
            'order'   => 'string',
            'active'  => 'boolean',
        ]);

        $country = Arr::get($validate, 'country');
        $order = Arr::get($validate, 'order');
        $active = Arr::get($validate, 'active');

        $q = Player::query();

        if ($country !== null) {
            $q->where('country_code', $country);
        }

        if ($order !== null) {
            $q->orderBy('rating', $order);

            if ($order === 'asc') {
                $q->whereNotNull('rating');
            }
        }

        if ($active !== null) {
            $q->where('updated_at', '>=', Carbon::now()->subWeek());
        }

        return $q
            ->limit(10)
            ->get()
            ->toArray();
    }
}
