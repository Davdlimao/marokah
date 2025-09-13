<?php

namespace App\Filament\Resources\Faturas\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class FaturasTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('vencimento')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                // 👇 usa a relação empresa
                Tables\Columns\TextColumn::make('empresa.nome')->label('Cliente')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($r) => $r && $r->status && method_exists($r->status, 'label') ? $r->status->label() : ((string) ($r->status ?? '-')))
                    ->color(fn ($r) => $r && $r->status && method_exists($r->status, 'color') ? $r->status->color() : 'gray'),
                Tables\Columns\TextColumn::make('total')->label('Total')->money('BRL', true)->sortable(),
                Tables\Columns\TextColumn::make('vencimento')->label('Vencimento')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'ABERTA' => 'Aberta',
                        'PAGA' => 'Paga',
                        'ATRASADA' => 'Atrasada',
                        'CANCELADA' => 'Cancelada',
                    ]),
            ])
            ->actions([
                EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
