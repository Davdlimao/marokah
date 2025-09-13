<?php

namespace App\Filament\Resources\Clientes\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column as ExcelColumn;
use Maatwebsite\Excel\Excel;

class ClientesTable
{
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
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                ExportAction::make('exportar')
                    ->label('Exportar')
                    ->exports([
                        ExcelExport::make('Clientes')
                            ->fromTable()
                            // Exclui alguma coluna que você não quer exportar:
                            ->except(['cpf_cnpj']) 
                            // Define colunas e cabeçalhos (ordem e labels ao seu gosto):
                            ->withColumns([
                                ExcelColumn::make('contrato')->heading('Contrato'),
                                ExcelColumn::make('tipo_pessoa')->heading('Tipo'),
                                ExcelColumn::make('razao_social')->heading('Cliente'),
                                ExcelColumn::make('status')->heading('Status'),
                                ExcelColumn::make('created_at')->heading('Criado em'),
                            ])
                            ->withFilename('empresas-clientes-' . now()->format('d-m-Y'))
                            ->withWriterType(Excel::XLSX),
                    ]),
            ])
            ->actions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->exports([
                        ExcelExport::make('Clientes selecionados')
                            ->fromTable()
                            // Exporta só as colunas que você quer (alternativa ao ->except())
                            ->only(['contrato','tipo_pessoa','razao_social','status','created_at'])
                            ->withColumns([
                                ExcelColumn::make('contrato')->heading('Contrato'),
                                ExcelColumn::make('tipo_pessoa')->heading('Tipo'),
                                ExcelColumn::make('razao_social')->heading('Cliente'),
                                ExcelColumn::make('status')->heading('Status'),
                                ExcelColumn::make('created_at')->heading('Criado em'),
                            ])
                            ->withFilename('clientes-selecionados-' . now()->format('Ymd-His'))
                            ->withWriterType(Excel::XLSX),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
                DeleteAction::make(),
            ])
            ->groupedBulkActions([
                RestoreBulkAction::make(),
                ForceDeleteBulkAction::make(),
                DeleteBulkAction::make(),
            ]);
    }
}
