<?php

namespace App\Filament\Resources\Faturas\Pages;

use App\Filament\Resources\Faturas\FaturaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFaturas extends ListRecords
{
    protected static string $resource = FaturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
