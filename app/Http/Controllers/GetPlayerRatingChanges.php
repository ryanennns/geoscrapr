<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GetPlayerRatingChanges extends Controller
{
    public function __invoke($id): Collection
    {
        $player = Player::query()
            ->where('id', $id)
            ->firstOrFail();

        $numberOfRatingChangesFromTheLastTwoWeeks = $player->ratingChanges()
            ->where('created_at', '>', Carbon::now()->subWeeks(2))
            ->count();

        return $player->ratingChanges()
            ->orderBy('created_at', 'desc')
            ->limit($numberOfRatingChangesFromTheLastTwoWeeks)
            ->get();
    }
}
