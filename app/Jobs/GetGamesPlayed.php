<?php

namespace App\Jobs;

use App\GeoGuessrHttp;
use App\Models\Player;
use App\Models\RankedGamesScannedUserIds;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetGamesPlayed implements ShouldQueue
{
    use Queueable;

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        try {
            $rankedGameScannedUserIds = RankedGamesScannedUserIds::query()->latest()->first();
            $ids = $rankedGameScannedUserIds?->user_ids ?? [];

            $count = Player::query()
                ->where('players.updated_at', '>=', now()->subWeek())
                ->whereNotIn('players.user_id', $ids)
                ->count();

            if ($count === 0) {
                Log::info('Wrapped around on duels played tracking');

                $rankedGameScannedUserIds = null;
                $ids = [];
            }

            $player = Player::query()
                ->select('players.id', 'players.user_id')
                ->where('players.updated_at', '>=', now()->subWeek())
                ->whereNotIn('players.user_id', $ids)
                ->orderBy('players.created_at', 'desc')
                ->limit(1)
                ->first();

            $response = Http::withHeaders(GeoGuessrHttp::HEADERS)
                ->get(GeoGuessrHttp::BASE_URL.'user/'.$player->user_id);

            $body = $response->body();

            $rankedDuelsGamesPlayed = $this->getStatValue($body, 'Ranked Duels', 'games');
            $totalSinglePlayerGamesPlayed = $this->getStatValue($body, 'Classic', 'games');
            $unrankedDuelsGamesPlayed = $this->getStatValue($body, 'Unranked Duels', 'games');
            $teamDuelsGamesPlayed = $this->getStatValue($body, 'Ranked Team Duels', 'games');
            $unrankedTeamDuelsGamesPlayed = $this->getStatValue($body, 'Unranked Team Duels', 'games');

            if (! $rankedGameScannedUserIds) {
                $rankedGameScannedUserIds = RankedGamesScannedUserIds::query()->create([
                    'user_ids' => [],
                ]);
            }

            $player->update([
                'ranked_duels_played' => $rankedDuelsGamesPlayed ?? 0,
                'total_single_player_games_played' => $totalSinglePlayerGamesPlayed ?? 0,
                'unranked_duels_played' => $unrankedDuelsGamesPlayed ?? 0,
                'ranked_team_duels_played' => $teamDuelsGamesPlayed ?? 0,
                'unranked_team_duels_played' => $unrankedTeamDuelsGamesPlayed ?? 0,
            ]);
            $rankedGameScannedUserIds
                ->update(['user_ids' => [...$rankedGameScannedUserIds->user_ids, $player->user_id]]);

        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
        } finally {
            GetGamesPlayed::dispatch()->delay(now()->addSeconds(5));
        }
    }

    private function getStatValue(string $body, string $heading, string $stat): ?string
    {
        $document = new DOMDocument;

        libxml_use_internal_errors(true);
        $document->loadHTML($body);
        libxml_clear_errors();

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query(
            '//h2[normalize-space() = "'.$heading.'"]'
            .'/ancestor::div[contains(@class, "widget_widgetInner")][1]'
            .'//div[span[normalize-space() = "'.$stat.'"]]/label'
        );

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        return trim($nodes->item(0)->textContent);
    }
}
