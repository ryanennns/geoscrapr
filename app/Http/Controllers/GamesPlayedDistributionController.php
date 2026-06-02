<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GamesPlayedDistributionController extends Controller
{
    public function __invoke(): Response
    {
        $rows = DB::query()
            ->fromSub(function ($query) {
                $query
                    ->from('players')
                    ->selectRaw('
                        COALESCE(ranked_duels_played, 0)
                        + COALESCE(unranked_duels_played, 0)
                        + COALESCE(ranked_team_duels_played, 0)
                        + COALESCE(unranked_team_duels_played, 0)
                        + COALESCE(single_player_games_played, 0) AS all_games_played,
                        rating
                    ')
                    ->whereNotNull('ranked_duels_played')
                    ->whereNotNull('unranked_duels_played')
                    ->whereNotNull('ranked_team_duels_played')
                    ->whereNotNull('unranked_team_duels_played');
            }, 'p')
            ->select(['all_games_played', 'rating'])
            ->orderByDesc('rating')
            ->get();

        $bands = [
            '1-250'    => [1, 250],
            '251-500'  => [251, 500],
            '501-1000' => [501, 1000],
            '1001+'    => [1001, null],
        ];

        $ratingMin = 200;
        $ratingMax = (int) $rows->max('rating');
        $bucketSize = 10;
        $smoothingWindow = 17;

        $ratingBuckets = range($ratingMin, $ratingMax, $bucketSize);

        $rawDatasets = [];
        $smoothedDatasets = [];
        $summary = [];

        foreach ($bands as $label => [$minGames, $maxGames]) {
            $playersInBand = $rows->filter(function ($row) use ($minGames, $maxGames) {
                if ($maxGames === null) {
                    return $row->all_games_played >= $minGames;
                }

                return $row->all_games_played >= $minGames
                    && $row->all_games_played <= $maxGames;
            });

            $ratings = $playersInBand
                ->pluck('rating')
                ->map(fn ($rating) => (int) $rating)
                ->sort()
                ->values();

            $summary[] = [
                'bucket'      => $label,
                'sample_size' => $ratings->count(),
                'mean_rating' => $ratings->count()
                    ? round($ratings->avg(), 2)
                    : null,
                'median_rating' => $this->median($ratings->all()),
            ];

            $countsByRatingBucket = array_fill_keys($ratingBuckets, 0);

            foreach ($playersInBand as $player) {
                $rating = (int) $player->rating;

                if ($rating < $ratingMin) {
                    continue;
                }

                $ratingBucket = intdiv($rating, $bucketSize) * $bucketSize;

                if (! array_key_exists($ratingBucket, $countsByRatingBucket)) {
                    $countsByRatingBucket[$ratingBucket] = 0;
                }

                $countsByRatingBucket[$ratingBucket]++;
            }

            $total = array_sum($countsByRatingBucket);

            $percentages = collect($countsByRatingBucket)
                ->map(fn ($count) => $total > 0 ? ($count / $total) * 100 : 0)
                ->values()
                ->all();

            $rawDatasets[] = [
                'label' => $label,
                'data'  => collect($ratingBuckets)
                    ->values()
                    ->map(function ($ratingBucket, $index) use ($percentages) {
                        return [
                            'x' => $ratingBucket,
                            'y' => round($percentages[$index], 4),
                        ];
                    })
                    ->all(),
                'tension'     => 0.35,
                'borderWidth' => 2.5,
                'pointRadius' => 0,
                'fill'        => false,
            ];

            $smoothedPercentages = $this->rollingAverage($percentages, $smoothingWindow);
            $smoothedPercentages = $this->renormalizeToHundred($smoothedPercentages);

            $smoothedDatasets[] = [
                'label' => $label,
                'data'  => collect($ratingBuckets)
                    ->values()
                    ->map(function ($ratingBucket, $index) use ($smoothedPercentages) {
                        return [
                            'x' => $ratingBucket,
                            'y' => round($smoothedPercentages[$index], 4),
                        ];
                    })
                    ->all(),
                'tension'     => 0.35,
                'borderWidth' => 2.5,
                'pointRadius' => 0,
                'fill'        => false,
            ];
        }

        return Inertia::render('GamesPlayedDistribution', [
            'chartData' => [
                'labels'   => $ratingBuckets,
                'datasets' => [
                    'raw'      => $rawDatasets,
                    'smoothed' => $smoothedDatasets,
                ],
                'summary'  => $summary,
                'meta'     => [
                    'rows'             => $rows->count(),
                    'rating_min'       => $ratingMin,
                    'rating_max'       => $ratingMax,
                    'bucket_size'      => $bucketSize,
                    'smoothing_window' => $smoothingWindow,
                    'y_axis'           => 'Percentage of players in category',
                    'x_axis'           => 'Rating',
                ],
            ],
        ]);
    }

    private function rollingAverage(array $values, int $window): array
    {
        $halfWindow = intdiv($window, 2);
        $count = count($values);
        $smoothed = [];

        for ($i = 0; $i < $count; $i++) {
            $start = max(0, $i - $halfWindow);
            $end = min($count - 1, $i + $halfWindow);

            $slice = array_slice($values, $start, $end - $start + 1);

            $smoothed[] = array_sum($slice) / count($slice);
        }

        return $smoothed;
    }

    private function renormalizeToHundred(array $values): array
    {
        $sum = array_sum($values);

        if ($sum <= 0) {
            return $values;
        }

        return array_map(
            fn ($value) => ($value / $sum) * 100,
            $values
        );
    }

    private function median(array $values): ?float
    {
        $count = count($values);

        if ($count === 0) {
            return null;
        }

        sort($values);

        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }

        return round(($values[$middle - 1] + $values[$middle]) / 2, 2);
    }
}
