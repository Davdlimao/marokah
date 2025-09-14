<?php

namespace App\Filament\Resources\Papeis\Pages;

use App\Filament\Resources\Papeis\PapeisResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePapeis extends CreateRecord
{
    protected static string $resource = PapeisResource::class;
    protected static ?string $title = 'Criar papel';
}
