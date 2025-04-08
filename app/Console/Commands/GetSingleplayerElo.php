<?php

namespace App\Console\Commands;

use App\GeoGuessrHttp;
use App\Models\Player;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetSingleplayerElo extends Command
{
    private const ENDPOINT = 'api/v4/ranked-system/ratings';
    private const LIMIT = 100;

    protected $signature = 'elo:singleplayer';
    protected $description = 'Command description';

    public function handle(): void
    {
        $keepFetching = true;
        $initPlayersCount = Player::query()->count();
        $initRatingChangeCount = Player::query()->where('rateable_type', Player::class)->count();
        for ($i = 0; $keepFetching; $i += 100) {
            try {
                $response = Http::withHeaders([
                    ...GeoGuessrHttp::HEADERS,
                    "cookie" => GeoGuessrHttp::cookieString()
                ])->get(GeoGuessrHttp::BASE_URL . self::ENDPOINT, [
                    'offset' => $i,
                    'limit'  => self::LIMIT
                ]);

                if (!$response->successful()) {
                    throw new \Exception("Request to GeoGuessr API failed with status {${$response->status}}");
                }

                $players = collect(json_decode($response->body()));

                if ($players->count() === 0) {
                    break;
                }

                $players->each(function ($player) {
                    Player::query()->updateOrCreate(['user_id' => $player->userId], [
                        'name'         => $player->nick,
                        'rating'       => $player->rating,
                        'country_code' => $player->countryCode,
                    ]);
                });

                $this->info('Players: ' . Player::query()->count() . ' $i = ' . $i);
            } catch (\Exception $e) {
                $this->error('An error occurred - ' . $e->getMessage());
                $keepFetching = false;
            }
        }

        $diff = Player::query()->count(0) - $initPlayersCount;
        $diffInRatingChanges = Player::query()->where('rateable_type', Player::class)->count() - $initRatingChangeCount;
        $this->info("Added $diff users, and $diffInRatingChanges ratings changed.");
    }
}
