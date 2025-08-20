<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Resources\Clientes\ClienteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;

    /** Chamado depois que o registro foi criado com sucesso */
    protected function afterCreate(): void
    {
        $this->redirect(
            static::getResource()::getUrl('edit', ['record' => $this->getRecord()])
        );
    }
}