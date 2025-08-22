<?php

namespace App\Filament\Resources\WorldCupMatches\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorldCupMatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('round')
                    ->required(),
                Select::make('player_one_id')
                    ->relationship('playerOne', 'name'),
                Select::make('player_two_id')
                    ->relationship('playerTwo', 'name'),
                Select::make('winner_id')
                    ->relationship('winner', 'name'),
                Toggle::make('is_live')
                    ->required(),
                DateTimePicker::make('finished_at')
                    ->required(),
                TextInput::make('link')
                    ->required(),
            ]);
    }
}
