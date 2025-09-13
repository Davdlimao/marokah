<?php

namespace App\Filament\Resources\Assinaturas\Pages;

use App\Filament\Resources\Assinaturas\AssinaturaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAssinatura extends EditRecord
{
    protected static string $resource = AssinaturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
