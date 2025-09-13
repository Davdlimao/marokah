<?php

namespace App\Filament\Resources\Assinaturas\Pages;

use App\Filament\Resources\Assinaturas\AssinaturaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssinaturas extends ListRecords
{
    protected static string $resource = AssinaturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
