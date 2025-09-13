<?php

namespace App\Filament\Resources\Planos\Pages;

use App\Filament\Resources\Planos\PlanoResource;
use Filament\Resources\Pages\ListRecords;

class ListPlanos extends ListRecords
{
    protected static string $resource = PlanoResource::class;

    protected static ?string $title = 'Planos';
}
