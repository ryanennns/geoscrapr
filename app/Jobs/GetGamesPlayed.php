<?php

namespace App\Jobs;

use App\GeoGuessrHttp;
use App\Models\Player;
use App\Models\RankedGamesScannedUserIds;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
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

            $player = $this->nextPlayer($rankedGameScannedUserIds);

            if (! $player && $rankedGameScannedUserIds) {
                Log::info('Wrapped around on duels played tracking');

                $rankedGameScannedUserIds = null;
                $player = $this->nextPlayer($rankedGameScannedUserIds);
            }

            if (! $player) {
                Log::info('No players found for duels played tracking');

                return;
            }

            $response = Http::withHeaders(GeoGuessrHttp::HEADERS)
                ->get(GeoGuessrHttp::BASE_URL . 'user/' . $player->user_id);

            $body = $response->body();

            $rankedDuelsGamesPlayed = $this->getStatValue($body, 'Ranked Duels', 'games');
            $singlePlayerGamesPlayed = $this->getStatValue($body, 'Classic', 'games');
            $unrankedDuelsGamesPlayed = $this->getStatValue($body, 'Unranked Duels', 'games');
            $teamDuelsGamesPlayed = $this->getStatValue($body, 'Ranked Team Duels', 'games');
            $unrankedTeamDuelsGamesPlayed = $this->getStatValue($body, 'Unranked Team Duels', 'games');

            if (! $rankedGameScannedUserIds) {
                $rankedGameScannedUserIds = RankedGamesScannedUserIds::query()->create([
                    'user_ids' => [],
                ]);
            }

            $player->update([
                'ranked_duels_played'        => $this->normalizeStatValue($rankedDuelsGamesPlayed),
                'single_player_games_played' => $this->normalizeStatValue($singlePlayerGamesPlayed),
                'unranked_duels_played'      => $this->normalizeStatValue($unrankedDuelsGamesPlayed),
                'ranked_team_duels_played'   => $this->normalizeStatValue($teamDuelsGamesPlayed),
                'unranked_team_duels_played' => $this->normalizeStatValue($unrankedTeamDuelsGamesPlayed),
            ]);
            $this->appendScannedPlayerId($rankedGameScannedUserIds, $player->id);

        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
        } finally {
            GetGamesPlayed::dispatch()->delay(now()->addSeconds(3));
        }
    }

    private function nextPlayer(?RankedGamesScannedUserIds $rankedGameScannedUserIds): ?Player
    {
        return $this->recentlyChangedPlayersQuery()
            ->select('players.id', 'players.user_id')
            ->tap(fn (Builder $query) => $this->whereUserIdNotScanned($query, $rankedGameScannedUserIds))
            ->orderBy('players.created_at', 'desc')
            ->limit(1)
            ->first();
    }

    private function recentlyChangedPlayersQuery(): Builder
    {
        return Player::query()
            ->whereHas('ratingChanges', function ($query) {
                $query->where('created_at', '>=', now()->subWeek());
            });
    }

    private function whereUserIdNotScanned(Builder $query, ?RankedGamesScannedUserIds $rankedGameScannedUserIds): void
    {
        if (! $rankedGameScannedUserIds) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            $query->whereRaw(<<<'SQL'
                NOT EXISTS (
                    SELECT 1
                    FROM ranked_games_scanned_user_ids AS scanned_rows
                    WHERE scanned_rows.id = ?
                        AND scanned_rows.user_ids @> jsonb_build_array(players.id)
                )
                SQL, [$rankedGameScannedUserIds->getKey()]);

            return;
        }

        $query->whereRaw(<<<'SQL'
            NOT EXISTS (
                SELECT 1
                FROM ranked_games_scanned_user_ids AS scanned_rows,
                    json_each(scanned_rows.user_ids) AS scanned_user_ids
                WHERE scanned_rows.id = ?
                    AND scanned_user_ids.value = players.id
            )
            SQL, [$rankedGameScannedUserIds->getKey()]);
    }

    private function appendScannedPlayerId(RankedGamesScannedUserIds $rankedGameScannedUserIds, string $playerId): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::update(
                <<<'SQL'
                    UPDATE ranked_games_scanned_user_ids
                    SET user_ids = user_ids || ?::jsonb,
                        updated_at = ?
                    WHERE id = ?
                    SQL,
                [json_encode([$playerId]), now(), $rankedGameScannedUserIds->id]
            );

            return;
        }

        $rankedGameScannedUserIds
            ->update(['user_ids' => [...$rankedGameScannedUserIds->user_ids, $playerId]]);
    }

    private function getStatValue(string $body, string $heading, string $stat): ?string
    {
        $document = new DOMDocument;

        libxml_use_internal_errors(true);
        $document->loadHTML($body);
        libxml_clear_errors();

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query(
            '//h2[normalize-space() = "' . $heading . '"]'
            . '/ancestor::div[contains(@class, "widget_widgetInner")][1]'
            . '//div[span[normalize-space() = "' . $stat . '"]]/label'
        );

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        return trim($nodes->item(0)->textContent);
    }

    private function normalizeStatValue(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return (int) str_replace(',', '', $value);
    }
}
