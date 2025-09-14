<?php

namespace App\Filament\Resources\Papeis\Pages;

use App\Filament\Resources\Papeis\PapeisResource;
use Filament\Resources\Pages\EditRecord;

class EditPapeis extends EditRecord
{
    protected static string $resource = PapeisResource::class;
    protected static ?string $title = 'Editar papel';
}
