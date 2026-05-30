<?php

namespace App\Jobs;

use App\GeoGuessrHttp;
use App\Models\Player;
use App\Models\RankedGamesScannedUserIds;
use DOMDocument;
use DOMXPath;
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
        $rankedGameScannedUserIds = RankedGamesScannedUserIds::query()->latest()->first();
        $ids = $rankedGameScannedUserIds?->user_ids ?? [];

        $count = Player::query()
            ->where('players.updated_at', '>=', now()->subWeek())
            ->whereNotIn('players.user_id', $ids)
            ->count();

        if ($count === 0) {
            Log::info("Wrapped around on duels played tracking");

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
            ->get(GeoGuessrHttp::BASE_URL . 'user/' . $player->user_id);

        $body = $response->body();

        $rankedDuelsGamesPlayed = $this->getStatValue($body, 'Ranked Duels', 'games');

        if (!$rankedGameScannedUserIds) {
            $rankedGameScannedUserIds = RankedGamesScannedUserIds::query()->create([
                'user_ids' => [],
            ]);
        }

        $player->update(['ranked_duels_played' => $rankedDuelsGamesPlayed ?? 0]);
        $rankedGameScannedUserIds
            ->update(['user_ids' => [...$rankedGameScannedUserIds->user_ids, $player->user_id]]);

        GetGamesPlayed::dispatch()->delay(now()->addSeconds(5));
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
}
