<?php

namespace App\Filament\Resources\Assinaturas\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

class AssinaturasTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('empresa.nome')
                    ->label('Cliente')
                    ->searchable()
                    ->formatStateUsing(fn ($value, $record) => $record->empresa?->nome ?? '-')
                ,
                Tables\Columns\TextColumn::make('plano.nome')
                    ->label('Plano')
                    ->searchable()
                    ->formatStateUsing(fn ($value, $record) => $record->plano?->nome ?? '-')
                ,
                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL', true),
                Tables\Columns\TextColumn::make('periodicidade')
                    ->label('Per.'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($value, $record) => $record && $record->status && method_exists($record->status, 'label') ? $record->status->label() : ((string) ($record->status ?? '-')))
                    ->color(fn ($record) => $record && $record->status && method_exists($record->status, 'color') ? $record->status->color() : 'gray'),
                Tables\Columns\TextColumn::make('proximo_vencimento')
                    ->label('Próx. vencimento')
                    ->state(fn ($record) => $record->proximo_vencimento)
                    ->date('d/m/Y'),
            ])
            ->actions([
                EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
