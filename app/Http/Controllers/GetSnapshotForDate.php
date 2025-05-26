<?php

namespace App\Http\Controllers;

use App\Models\EloSnapshot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GetSnapshotForDate extends Controller
{
    public function __invoke(Request $request): array
    {
        $validated = $request->validate([
            'date' => [
                'required', Rule::date()->format('Y-m-d')
            ]
        ]);
        $date = $validated['date'];

        $solo = EloSnapshot::query()
            ->whereDate('date', $date)
            ->where('type', EloSnapshot::TYPE_ELO_RANGE)
            ->where('gamemode', 'solo')
            ->orderByDesc('date')
            ->firstOrFail();

        $team = EloSnapshot::query()
            ->whereDate('date', $date)
            ->where('type', EloSnapshot::TYPE_ELO_RANGE)
            ->where('gamemode', 'team')
            ->orderByDesc('date')
            ->firstOrFail();

        return [
            'data' => [
                'solo' => [
                    'date'    => Carbon::parse($solo->date)->format('Y-m-d'),
                    'buckets' => json_decode($solo->buckets, true),
                    'n'       => $solo->n,
                    'type'    => $solo->type,
                ],
                'team' => [
                    'date'    => Carbon::parse($team->date)->format('Y-m-d'),
                    'buckets' => json_decode($team->buckets, true),
                    'n'       => $team->n,
                    'type'    => $team->type,
                ]
            ]
        ];
    }
}
