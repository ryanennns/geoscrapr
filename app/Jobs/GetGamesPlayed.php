<?php

namespace App\Jobs;

use App\GeoGuessrHttp;
use App\Models\Player;
use DOMDocument;
use DOMXPath;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class GetGamesPlayed implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $cursor)
    {
        //
    }

    public function handle(): void
    {
        $player = Player::query()
            ->select('players.id', 'players.user_id')
            ->where('players.updated_at', '>=', now()->subWeek())
            ->orderBy('players.user_id', 'desc')
            ->offset($this->cursor)
            ->limit(1)
            ->first();

        $response = Http::withHeaders(GeoGuessrHttp::HEADERS)
            ->get(GeoGuessrHttp::BASE_URL . 'user/' . $player->user_id);

        $body = $response->body();

        // <label style="--fs:var(--font-size-18);--lh:var(--line-height-18)" class="label_label__LA0MZ shared_boldWeight__VXL2d label_italic__RNYlk label_uppercase__7GXBt">219</label>
        $rankedDuelsGamesPlayed = $this->getStatValue($body, 'Ranked Duels', 'games');

        $player->update(['ranked_duels_played' => $rankedDuelsGamesPlayed]);
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
