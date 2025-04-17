<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GetTeamRatingChanges extends Controller
{
    public function __invoke($id): Collection
    {
        $team = Team::query()->findOrFail($id);

        return $team->ratingChanges()
            ->select('rating_changes.*')
            ->join(DB::raw('(
                SELECT MAX(id) as id
                FROM rating_changes
                WHERE rateable_id = ?
                GROUP BY DATE(created_at)
            ) as latest'), 'rating_changes.id', '=', 'latest.id')
            ->addBinding($team->id, 'select')
            ->get();
    }
}
