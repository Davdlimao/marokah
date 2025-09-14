<?php

namespace App\Filament\Resources\Papeis\Pages;

use App\Filament\Resources\Papeis\PapeisResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPapeis extends ViewRecord
{
    protected static string $resource = PapeisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
