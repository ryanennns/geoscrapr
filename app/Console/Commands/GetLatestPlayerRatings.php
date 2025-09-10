<?php

namespace App\Console\Commands;

use App\GeoGuessrHttp;
use App\Models\Player;
use App\Models\RatingChange;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetLatestPlayerRatings extends Command
{
    private const ENDPOINT = 'api/v4/ranked-system/ratings';
    private const LIMIT = 100;

    private const MOVING_GAMETYPE = 'StandardDuels';
    private const NO_MOVE_GAMETYPE = 'NoMoveDuels';
    private const NMPZ_GAMETYPE = 'NmpzDuels';

    private const GAMETYPE_COLUMN_MAP = [
        null                   => 'rating',
        self::MOVING_GAMETYPE  => 'moving_rating',
        self::NO_MOVE_GAMETYPE => 'no_move_rating',
        self::NMPZ_GAMETYPE    => 'nmpz_rating',
    ];

    protected $signature = 'elo:singleplayer';
    protected $description = 'Command description';

    public function handle(): void
    {
        $initPlayersCount = Player::query()->count();
        $initRatingChangeCount = RatingChange::query()->where('rateable_type', Player::class)->count();

        collect([null, self::MOVING_GAMETYPE, self::NO_MOVE_GAMETYPE, self::NMPZ_GAMETYPE])
            ->each(function ($gameMode) {
                $this->fetchPaginatedPlayerData($gameMode);
            });

        $diff = Player::query()->count() - $initPlayersCount;
        $diffInRatingChanges = RatingChange::query()->where('rateable_type', Player::class)->count() - $initRatingChangeCount;
        $this->info("Added $diff users, and $diffInRatingChanges ratings changed.");

        Log::info("Added $diff users, and $diffInRatingChanges ratings changed.");
    }

    public function fetchPaginatedPlayerData(?string $gameMode): void
    {
        $keepFetching = true;
        for ($i = 0; $keepFetching; $i += 100) {
            try {
                $response = Http::withHeaders([
                    ...GeoGuessrHttp::HEADERS,
                    "cookie" => GeoGuessrHttp::cookieString()
                ])->get(GeoGuessrHttp::BASE_URL . self::ENDPOINT, [
                    'offset'   => $i,
                    'limit'    => self::LIMIT,
                    'gameMode' => $gameMode,
                ]);

                if (!$response->successful()) {
                    $this->error("Responded with $response->status");
                    throw new \Exception("Request to GeoGuessr API failed with status {${$response->status}}");
                }

                $players = collect(json_decode($response->body()));

                if ($players->count() === 0) {
                    break;
                }

                $players->each(function ($player) use ($gameMode) {
                    $properties = array_merge(
                        ['name' => $player->nick, 'country_code' => $player->countryCode],
                        [self::GAMETYPE_COLUMN_MAP[$gameMode] => $player->rating]
                    );

                    Player::query()->updateOrCreate(['user_id' => $player->userId], $properties);
                });

                $this->info('Players: ' . Player::query()->count() . ' $i = ' . $i . ' gameMode = ' . $gameMode ?? 'all');
            } catch (\Exception $e) {
                $this->error('An error occurred - ' . $e->getMessage());
                $keepFetching = false;

                Log::error($e->getMessage());
            }
        }
    }
}
