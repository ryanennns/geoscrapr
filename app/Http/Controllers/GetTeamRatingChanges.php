<?php

namespace App\Http\Controllers;

use App\Http\Resources\RatingHistoryResource;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetTeamRatingChanges extends Controller
{
    public function __invoke($id): AnonymousResourceCollection
    {
        $team = Team::query()
            ->where('id', $id)
            ->firstOrFail();

        $numberOfRatingChangesFromTheLastTwoWeeks = $team->ratingChanges()
            ->where('created_at', '>', Carbon::now()->subWeeks(8))
            ->count();

        return RatingHistoryResource::collection(
            $team->ratingChanges()
                ->orderBy('created_at', 'desc')
                ->limit($numberOfRatingChangesFromTheLastTwoWeeks + 1)
                ->get()
        );
    }
}
