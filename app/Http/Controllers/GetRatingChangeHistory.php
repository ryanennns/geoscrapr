<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\RatingChange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetRatingChangeHistory extends Controller
{
    public function __invoke($user_id): Collection
    {
        $player = Player::query()
            ->where('user_id', $user_id)
            ->firstOrFail();

        $playerId = $player->id;

        return $player->ratingChanges()
            ->select('rating_changes.*')
            ->join(DB::raw('(
                SELECT MAX(id) as id
                FROM rating_changes
                WHERE rateable_id = ?
                GROUP BY DATE(created_at)
            ) as latest'), 'rating_changes.id', '=', 'latest.id')
            ->addBinding($playerId, 'select') // bind the ? in the subquery
            ->get();
    }
}
