<?php

namespace App\Jobs;

use App\GeoGuessrHttp;
use App\Models\Player;
use App\Models\RatingChange;
use App\Models\Team;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateTeamRatings implements ShouldQueue
{
    use Queueable;

    private const LIMIT = 100;
    private const ENDPOINT = 'api/v4/ranked-team-duels/ratings';

    public int $timeout = 900;

    public function handle(): void
    {
        $keepFetching = true;
        $initPlayersCount = Player::query()->count();
        $initTeamsCount = Team::query()->count();
        $initRatingChangeCount = RatingChange::query()->where('rateable_type', Team::class)->count();
        for ($i = 0; $keepFetching; $i += 100) {
            try {
                $response = Http::withHeaders([
                    ...GeoGuessrHttp::HEADERS,
                    "cookie" => GeoGuessrHttp::cookieString(),
                ])->get(GeoGuessrHttp::BASE_URL . self::ENDPOINT, [
                    'offset' => $i,
                    'limit'  => self::LIMIT
                ]);

                if (!$response->successful()) {
                    throw new \Exception("Request to GeoGuessr API failed with status {${$response->status()}}");
                }

                $teams = collect(json_decode($response->body()));

                if ($teams->count() === 0) {
                    break;
                }

                $teams->each(function ($team) {
                    collect($team->members)->each(function ($player) {
                        Player::query()->updateOrCreate(['user_id' => $player->userId], [
                            'name'         => $player->nick,
                            'user_id'      => $player->userId,
                            'country_code' => $player->countryCode,
                        ]);
                    });

                    Team::query()->updateOrCreate(['team_id' => $team->teamId], [
                        'team_id'  => $team->teamId,
                        'rating'   => $team->rating,
                        'name'     => $team->teamName,
                        'player_a' => $team->members[0]->userId,
                        'player_b' => $team->members[1]->userId,
                    ]);
                });
            } catch (\Exception $e) {
                $keepFetching = false;
            }
        }

        $playerDiff = Player::query()->count() - $initPlayersCount;
        $teamDiff = Team::query()->count() - $initTeamsCount;
        $ratingChangeDiff = RatingChange::query()->where('rateable_type', Team::class)->count() - $initRatingChangeCount;

        Log::info("Added $playerDiff players, $teamDiff teams, and $ratingChangeDiff ratings changed.");
    }
}
