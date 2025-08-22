<?php

namespace App\Filament\Resources\WorldCupMatches\Schemas;

use App\Models\Player;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorldCupMatchForm
{
    public const WORLD_CUP_PLAYER_IDS = [
        '57d301d409f2efcce834fc94',
        '601d17c1d565030001440b8d',
        '5de7a59044d2a42f78156b33',
        '603b1b0d5cdb1b0001bbf19e',
        '5bf491faaac55b998458ed9a',
        '5a973147afad0f2a68438531',
        '5e2e983722bbda85a40e9009',
        '57ebb537a52b273ab0162ed8',
        '5b51062a4010740f7cd91dd5',
        '5e5fcc1326bbda5284e824cf',
        '633a62ba560e8238dea97807',
        '5c03eed1b5b94ba700403005',
        '59d0b74bd8fe1d5b30651962',
        '635c171d190621fb60d8bb08',
        '55abc223ffb93d3658e4b76c',
        '5b4899f5b56fe41a1831bba4',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('round')->required(),

            // Player 1 (async search; no preload)
            Select::make('player_one_id')
                ->label('Player 1')
                ->native(false)
                ->searchable()
                ->placeholder('Search player…')
                ->getSearchResultsUsing(function (string $search): array {
                    $search = trim($search);
                    if (mb_strlen($search) < 2) {
                        return [];
                    }

                    return Player::query()
                        ->where('name', 'like', "%{$search}%")
                        ->whereIn('user_id', self::WORLD_CUP_PLAYER_IDS)
                        ->limit(20)
                        ->pluck('name', 'user_id')
                        ->toArray();
                })
                ->getOptionLabelUsing(fn($value) => Player::query()->where('user_id', $value)->value('name') ?? (string)$value
                ),

            // Player 2
            Select::make('player_two_id')
                ->label('Player 2')
                ->native(false)
                ->searchable()
                ->placeholder('Search player…')
                ->getSearchResultsUsing(function (string $search): array {
                    $search = trim($search);
                    if (mb_strlen($search) < 2) {
                        return [];
                    }

                    return Player::query()
                        ->where('name', 'like', "%{$search}%")
                        ->whereIn('user_id', self::WORLD_CUP_PLAYER_IDS)
                        ->limit(20)
                        ->pluck('name', 'user_id')
                        ->toArray();
                })
                ->getOptionLabelUsing(fn($value) => Player::query()->where('user_id', $value)->value('name') ?? (string)$value
                ),

            // Winner
            Select::make('winner_id')
                ->label('Winner')
                ->native(false)
                ->searchable()
                ->placeholder('Search player…')
                ->getSearchResultsUsing(function (string $search): array {
                    $search = trim($search);
                    if (mb_strlen($search) < 2) {
                        return [];
                    }

                    return Player::query()
                        ->where('name', 'like', "%{$search}%")
                        ->whereIn('user_id', self::WORLD_CUP_PLAYER_IDS)
                        ->limit(20)
                        ->pluck('name', 'user_id')
                        ->toArray();
                })
                ->getOptionLabelUsing(fn($value) => Player::query()->where('user_id', $value)->value('name') ?? (string)$value
                ),

            Toggle::make('is_live')->required(),
            DateTimePicker::make('finished_at')->required(),
            TextInput::make('link')->required(),
        ]);
    }
}
