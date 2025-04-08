<?php

namespace App\Console\Commands;

use App\GeoGuessrHttp;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetTeamsElo extends Command
{
    private const LIMIT = 100;
    private const ENDPOINT = 'api/v4/ranked-team-duels/ratings';

    protected $signature = 'elo:teams';

    protected $description = 'Command description';

    public function handle(): void
    {
        $keepFetching = true;
        $initPlayersCount = Player::query()->count();
        $initTeamsCount = Team::query()->count();
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
                    throw new \Exception("Request to GeoGuessr API failed with status {${$response->status}}");
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

                $this->info(
                    'Players: ' . Player::query()->count() . ', Teams: ' . Team::query()->count() . ' $i = ' . $i
                );
            } catch (\Exception $e) {
                $this->error('An error occurred - ' . $e->getMessage());
                $keepFetching = false;
            }
        }

        $playerDiff = Player::query()->count() - $initPlayersCount;
        $teamDiff = Team::query()->count() - $initTeamsCount;
        $this->info("Added $playerDiff users");
        $this->info("Added $teamDiff teams");
    }
}
