<?php

namespace App\Filament\Resources\Usuarios\Tables;

use App\Models\User;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\DeleteBulkAction;

class UsuariosTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->state(fn ($r) => $r?->email_verified_at ? 'Sim' : 'Não')
                    ->badge()
                    ->color(fn ($r) => $r?->email_verified_at ? 'success' : 'gray')
                    ->alignCenter(),

                // Só exibe se a coluna existir
                ...(
                    Schema::hasColumn('users', 'is_active')
                        ? [
                            TextColumn::make('is_active')
                                ->label('Ativo')
                                ->state(fn ($r) => $r?->is_active ? 'Sim' : 'Não')
                                ->badge()
                                ->color(fn ($r) => $r?->is_active ? 'success' : 'danger')
                                ->alignCenter(),
                        ]
                        : []
                ),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Verificado')
                    ->nullable(),

                // Só exibe se a coluna existir
                ...(
                    Schema::hasColumn('users', 'is_active')
                        ? [
                            TernaryFilter::make('is_active')
                                ->label('Ativo')
                                ->nullable(),
                        ]
                        : []
                ),
            ])
            ->actions([
                Action::make('resetPassword')
                    ->label('Resetar senha')
                    ->icon('heroicon-o-key')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $new = Str::random(10);
                        $record->forceFill(['password' => Hash::make($new)])->save();
                        \Filament\Notifications\Notification::make()
                            ->title('Nova senha gerada')
                            ->body("Anote a senha temporária: **{$new}**")
                            ->success()
                            ->seconds(12)
                            ->send();
                    }),

                EditAction::make()
                    ->label('Editar'),

                Action::make('delete')
                    ->label('Excluir')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(fn (User $r) => $r->delete())
                    ->visible(fn ($r) => $r && $r instanceof User && $r->getKey() !== auth()->id())
                    ->tooltip('Não é possível excluir a si mesmo'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Excluir selecionados')
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
