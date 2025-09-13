<?php

namespace App\Filament\Resources\Planos\Pages;

use App\Filament\Resources\Planos\PlanoResource;
use Filament\Resources\Pages\EditRecord;

class EditPlano extends EditRecord
{
    protected static string $resource = PlanoResource::class;

    protected static ?string $title = 'Editar plano';
}
