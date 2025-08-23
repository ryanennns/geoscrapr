<?php

namespace App\Filament\Resources\WorldCupMatches;

use App\Filament\Resources\WorldCupMatches\Pages\CreateWorldCupMatch;
use App\Filament\Resources\WorldCupMatches\Pages\EditWorldCupMatch;
use App\Filament\Resources\WorldCupMatches\Pages\ListWorldCupMatches;
use App\Filament\Resources\WorldCupMatches\Schemas\WorldCupMatchForm;
use App\Filament\Resources\WorldCupMatches\Tables\WorldCupMatchesTable;
use App\Models\WorldCupMatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorldCupMatchResource extends Resource
{
    protected static ?string $model = WorldCupMatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'round';

    public static function form(Schema $schema): Schema
    {
        return WorldCupMatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorldCupMatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorldCupMatches::route('/'),
            'create' => CreateWorldCupMatch::route('/create'),
            'edit' => EditWorldCupMatch::route('/{record}/edit'),
        ];
    }
}
