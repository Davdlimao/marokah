<?php

namespace App\Filament\Resources\Clientes\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ClientesTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                //TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('contrato')->label('Contrato')->copyable(),
                TextColumn::make('tipo_pessoa')->label('Tipo')->badge(),
                TextColumn::make('razao_social')->label('Cliente')->searchable()->limit(40),
                TextColumn::make('cpf_cnpj')->label('CPF/CNPJ')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Criado em')->date('d/m/Y')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Excluir selecionados'),
                ]),
            ]);
    }
}
