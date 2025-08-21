<?php

namespace App\Filament\Resources\Clientes\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

/**
 * Classe responsável por definir a tabela de clientes no painel Filament.
 */
class ClientesTable
{
    /**
     * Configura e retorna a instância da tabela de clientes.
     *
     * @param Table $table Instância da tabela Filament.
     * @return Table Tabela configurada com colunas, ações e ações em massa.
     */
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contrato')->label('Contrato')->copyable(),
                TextColumn::make('tipo_pessoa')->label('Tipo')->badge(),
                TextColumn::make('razao_social')->label('Cliente')->searchable()->limit(40),
                TextColumn::make('cpf_cnpj')->label('CPF/CNPJ')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Criado em')->date('d/m/Y')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([]) // Nenhum filtro definido para esta tabela
            ->actions([
                EditAction::make()->label('Editar'), // Ação para editar registros
                DeleteAction::make()->label('Excluir'), // Ação para excluir registros
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Excluir selecionados'), // Ação em massa para excluir múltiplos registros
                ]),
            ]);
    }
}
