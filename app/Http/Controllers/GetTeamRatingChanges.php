<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GetTeamRatingChanges extends Controller
{
    public function __invoke($id): Collection
    {
        $team = Team::query()
            ->where('id', $id)
            ->firstOrFail();

        $numberOfRatingChangesFromTheLastTwoWeeks = $team->ratingChanges()
            ->where('created_at', '>', Carbon::now()->subWeeks(2))
            ->count();

        return $team->ratingChanges()
            ->orderBy('created_at', 'desc')
            ->limit($numberOfRatingChangesFromTheLastTwoWeeks)
            ->get();
    }
}
