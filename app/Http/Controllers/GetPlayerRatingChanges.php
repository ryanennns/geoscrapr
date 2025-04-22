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

        $numberOfRatingChangesFromTheLastTwoWeeks = $player->ratingChanges()
            ->where('created_at', '>', Carbon::now()->subWeeks(2))
            ->count();

        return RatingHistoryResource::collection(
            $player->ratingChanges()
                ->orderBy('created_at', 'desc')
                ->limit($numberOfRatingChangesFromTheLastTwoWeeks + 1)
                ->get()
        );
    }
}
