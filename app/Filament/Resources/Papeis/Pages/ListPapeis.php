<?php

namespace App\Filament\Resources\Papeis\Pages;

use App\Filament\Resources\Papeis\PapeisResource;
use Filament\Resources\Pages\ListRecords;

class ListPapeis extends ListRecords
{
    protected static string $resource = PapeisResource::class;
    protected static ?string $title = 'Papéis';
}
