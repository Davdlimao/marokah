<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuariosResource;
use Filament\Resources\Pages\EditRecord;

class EditUsuarios extends EditRecord
{
    protected static string $resource = UsuariosResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Usuário atualizado!';
    }
}
