<?php

namespace App\Filament\Resources\WorldCupMatches\Pages;

use App\Filament\Resources\WorldCupMatches\WorldCupMatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorldCupMatches extends ListRecords
{
    protected static string $resource = WorldCupMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
