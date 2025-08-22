<?php

namespace App\Filament\Resources\WorldCupMatches\Pages;

use App\Filament\Resources\WorldCupMatches\WorldCupMatchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorldCupMatch extends EditRecord
{
    protected static string $resource = WorldCupMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
