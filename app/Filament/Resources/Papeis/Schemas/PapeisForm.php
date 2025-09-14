<?php

namespace App\Filament\Resources\Papeis\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class PapeisForm
{
    public static function make(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome do papel (interno)')
                ->required()
                ->unique(ignorable: fn ($record) => $record)
                ->disabled(fn ($record) => $record?->bloqueado),

            Select::make('escopo')
                ->label('Escopo')
                ->options(['sistema' => 'Sistema', 'cliente' => 'Cliente'])
                ->required()
                ->disabled(fn ($record) => $record?->bloqueado),

            Textarea::make('descricao')->label('Descrição')->rows(3),

            Toggle::make('bloqueado')->label('Bloqueado')->disabled()
                ->helperText('Impede renomear/excluir. Controlado via seeder.'),
        ])->columns(2);
    }
}
