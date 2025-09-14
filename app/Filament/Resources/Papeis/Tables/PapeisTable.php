<?php

namespace App\Filament\Resources\Papeis\Tables;

use App\Models\Papel;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;


class PapeisTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Papel')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('escopo')->label('Escopo')->colors(['primary'])->sortable(),
                Tables\Columns\IconColumn::make('bloqueado')->label('Bloqueado')->boolean(),
                Tables\Columns\TextColumn::make('users_count')->label('Usuários')->counts('users')->sortable(),
                Tables\Columns\TextColumn::make('permissions_count')->label('Permissões')->counts('permissions')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('escopo')
                    ->label('Escopo')
                    ->options(['sistema' => 'Sistema', 'cliente' => 'Cliente']),
            ])
            ->actions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()
                    ->label('Excluir')
                    ->visible(fn (Papel $record) => !$record->bloqueado && $record->name !== 'superadmin'),
            ])
            ->bulkActions([]); // sem exclusão em massa
    }
}
