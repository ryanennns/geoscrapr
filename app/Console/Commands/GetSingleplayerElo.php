<?php

namespace App\Console\Commands;

use App\GeoGuessrHttp;
use App\Models\Player;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class GetSingleplayerElo extends Command
{
    private const BASE_URL = 'https://www.geoguessr.com/api/v4/ranked-system/ratings';
    private const LIMIT = 100;

    protected $signature = 'elo:singleplayer';
    protected $description = 'Command description';

    public function handle(): void
    {
        $keepFetching = true;
        $players = Player::query()->count();
        for ($i = 0; $keepFetching; $i += 100) {
            try {
                $deviceToken = Config::get('geo.device_token');
                $cfuvid = Config::get('geo.cfuvid');
                $ncfa = Config::get('geo.ncfa');
                $session = Config::get('geo.session');
                $response = Http::withHeaders([
                    ...GeoGuessrHttp::HEADERS,
                    "cookie" => "devicetoken=$deviceToken; _cfuvid=$cfuvid; _ncfa=$ncfa; _session=$session",
                ])->get(self::BASE_URL, [
                    'offset' => $i,
                    'limit'  => self::LIMIT
                ]);

                if (!$response->successful()) {
                    throw new \Exception('ooga');
                }

                $c = collect(json_decode($response->body()));

                if ($c->count() === 0) {
                    break;
                }

                $c->each(function ($player) {
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

        $diff = Player::query()->count(0) - $players;
        $this->info("Added $diff users");
    }
}
