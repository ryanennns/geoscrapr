<?php

namespace App\Jobs;

use App\GeoGuessrHttp;
use App\Models\Player;
use App\Models\RatingChange;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UpdatePlayerRatings implements ShouldQueue
{
    use Queueable;

    public const ENDPOINT = 'api/v4/ranked-system/ratings';
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

    public int $timeout = 900;

    public function geoGuessrKeyToTableKey(string|null $key)
    {
        return match ($key) {
            self::MOVING_GAMETYPE => 'moving',
            self::NO_MOVE_GAMETYPE => 'no_move',
            self::NMPZ_GAMETYPE => 'nmpz',
            null => 'overall',
        };
    }

    public function handle(): void
    {
        collect([null, self::MOVING_GAMETYPE, self::NO_MOVE_GAMETYPE, self::NMPZ_GAMETYPE])
            ->each(function ($gameMode) {
                $this->fetchPaginatedPlayerData($gameMode);
            });
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
                    throw new \Exception("Request to GeoGuessr API failed with status {${$response->status()}}");
                }

                $players = collect(json_decode($response->body()));

                if ($players->count() === 0) {
                    break;
                }

                $ids = $players->pluck('userId')->all();
                $existing = Player::query()
                    ->whereIn('user_id', $ids)
                    ->get([
                        'id',
                        'user_id',
                        self::GAMETYPE_COLUMN_MAP[$gameMode],
                    ])
                    ->keyBy('user_id');

                $ratingColumn = self::GAMETYPE_COLUMN_MAP[$gameMode];

                $ratingChanges = [];
                $upsertRows = [];
                foreach ($players as $player) {
                    $userId = $player->userId;
                    $newRating = $player->rating;

                    if ((Arr::get($existing, $userId)?->$ratingColumn) !== $newRating) {
                        $ratingChanges[] = [
                            'user_id' => $userId,
                            'id'            => Str::uuid()->toString(),
                            'rateable_type' => Player::class,
                            'rateable_id'   => Arr::get($existing, $userId)?->id,
                            'rating'        => $newRating,
                            'type'          => $this->geoGuessrKeyToTableKey($gameMode),
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ];
                    }

                    $upsertRows[] = [
                        'user_id'      => $userId,
                        'name'         => $player->nick,
                        'country_code' => $player->countryCode,
                        $ratingColumn  => $newRating,
                        'updated_at'   => now(),
                    ];
                }

                Player::query()->upsert(
                    $upsertRows,
                    ['user_id'],
                    ['name', 'country_code', $ratingColumn, 'updated_at']
                );

                $ratingChanges = collect($ratingChanges)
                    ->map(function ($item) {
                        if (!is_null($item['rateable_id'])) {
                            unset($item['user_id']);

                            return $item;
                        }

                        $rateable = Player::query()->where('user_id', $item['user_id'])->first();

                        unset($item['user_id']);

                        return [...$item, 'rateable_id' => $rateable->getKey()];
                    })->toArray();

                RatingChange::query()->insert($ratingChanges);
            } catch (\Exception $e) {
                $keepFetching = false;

                Log::error($e->getMessage());
            }
        }
    }
}
