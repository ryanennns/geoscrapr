<?php

namespace App\Http\Controllers;

use App\Http\Resources\RatingHistoryResource;
use App\Models\Player;
use App\Models\RatingChange;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetPlayerRatingChanges extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $id = $request->route('id');
        $type = $request->query('type');

        $player = Player::query()
            ->where('id', $id)
            ->firstOrFail();

        $numberOfRatingChangesFromTheLastTwoWeeks = $player->ratingChanges()
            ->where('created_at', '>', Carbon::now()->subWeeks(16))
            ->count();

        $query = $player->ratingChanges()->orderBy('created_at', 'desc');

        if ($type !== null) {
            $query->where('type', $type);
        }

        return RatingHistoryResource::collection(
            $query->limit($numberOfRatingChangesFromTheLastTwoWeeks + 1)->get()
        );
    }
}
