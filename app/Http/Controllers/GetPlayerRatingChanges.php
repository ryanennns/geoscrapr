<?php

namespace App\Http\Controllers;

use App\Http\Resources\RatingHistoryResource;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetPlayerRatingChanges extends Controller
{
    public function __invoke($id): AnonymousResourceCollection
    {
        $player = Player::query()
            ->where('id', $id)
            ->firstOrFail();

        $ratingChanges = $player->ratingChanges()
            ->where('created_at', '>', Carbon::now()->subWeeks(8))
            ->get();

        $oldestInDataSet = $ratingChanges->sortBy('created_at')
            ->first()
            ->created_at;

        $oldestInGeneral = $player->ratingChanges()
            ->oldest()
            ->select('created_at')
            ->first()
            ->created_at;

        $includesOldest = true;
        if (
            $oldestInDataSet &&
            $oldestInGeneral &&
            Carbon::parse($oldestInGeneral) < Carbon::parse($oldestInDataSet)
        ) {
            $includesOldest = false;
        }

        return RatingHistoryResource::collection($ratingChanges)
            ->additional([
                'includes_oldest' => $includesOldest,
            ]);
    }
}
