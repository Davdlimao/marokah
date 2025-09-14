<?php

namespace App\Filament\Resources\Usuarios\Tables;

use App\Models\User;
use App\Models\Convite;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Filament\Actions\DeleteBulkAction;
use App\Mail\ConviteCadastroMailable;
use App\Support\EmailRuntime;
use Carbon\Carbon;

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

                // Papéis (Spatie)
                TextColumn::make('roles.name')
                    ->label('Papéis')
                    ->badge()
                    ->separator(', ')
                    ->toggleable()
                    ->limitList(3),

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

                // Troca obrigatória de senha
                ...(
                    Schema::hasColumn('users', 'must_change_password')
                        ? [
                            TextColumn::make('must_change_password')
                                ->label('Troca pendente')
                                ->state(fn ($r) => ($r?->must_change_password ?? false) ? 'Sim' : 'Não')
                                ->badge()
                                ->color(fn ($r) => ($r?->must_change_password ?? false) ? 'warning' : 'gray')
                                ->alignCenter(),
                        ]
                        : []
                ),
                
                // Último acesso
                TextColumn::make('last_login_at')
                    ->label('Último acesso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                // IP do último acesso
                TextColumn::make('last_login_ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Status do convite (se houver convite para este e-mail)
                TextColumn::make('convite_status')
                    ->label('Convite')
                    ->state(function (User $r) {
                        $c = Convite::where('email', $r->email)->latest()->first();
                        if (!$c) {
                            return '—';
                        }
                        if ($c->usado_em) {
                            return 'usado';
                        }
                        if ($c->expira_em && Carbon::parse($c->expira_em)->isPast()) {
                            return 'expirado';
                        }
                        return 'pendente';
                    })
                    ->badge()
                    ->color(function (User $r) {
                        $c = Convite::where('email', $r->email)->latest()->first();
                        if (!$c) return 'gray';
                        if ($c->usado_em) return 'gray';
                        if ($c->expira_em && Carbon::parse($c->expira_em)->isPast()) return 'danger';
                        return 'warning';
                    })
                    ->alignCenter(),

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

                // Filtrar por papel (via Spatie)
                SelectFilter::make('papel')
                    ->label('Papel')
                    ->relationship('roles', 'name')
                    ->preload(),
            ])
            ->recordActions([
                // Reenviar convite (se houver convite pendente)
                Action::make('reenviarConvite')
                    ->label('Reenviar convite')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(function (User $record) {
                        $c = Convite::where('email', $record->email)->latest()->first();
                        return $c && !$c->usado_em && (!$c->expira_em || Carbon::parse($c->expira_em)->isFuture());
                    })
                    ->action(function (User $record) {
                        // Removido: EmailRuntime::applyFromSettings(); pois o método não existe

                        $c = Convite::where('email', $record->email)->latest()->first();

                        // se não existir ou já usado/expirado, cria um novo
                        if (!$c || $c->usado_em || ($c->expira_em && Carbon::parse($c->expira_em)->isPast())) {
                            $c = Convite::create([
                                'email'            => $record->email,
                                'nome'             => $record->name,
                                'papeis'           => null, // pode preencher com os papéis atuais se desejar
                                'token_hash'       => '',
                                'expira_em'        => now()->addDays(7),
                                'convidado_por_id' => auth()?->user()?->getKey(),
                            ]);
                        }

                        $tokenPlano   = Str::random(64);
                        $c->token_hash = hash('sha256', $tokenPlano);
                        $c->expira_em  = now()->addDays(7);
                        $c->save();

                        $url = URL::temporarySignedRoute(
                            'convites.mostrar',
                            $c->expira_em ?? now()->addDays(7),
                            ['convite' => $c->id, 'token' => $tokenPlano]
                        );

                        // Envia o convite por e-mail
                        Mail::to($c->email)->send(new ConviteCadastroMailable($c, $url));

                        \Filament\Notifications\Notification::make()
                            ->title('Convite reenviado!')
                            ->success()
                            ->send();
                    }),

                // Resetar senha
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

                // Bloquear/desbloquear usuário
                Action::make('bloquear')
                    ->label(fn (User $u) => $u->is_active ? 'Bloquear' : 'Desbloquear')
                    ->icon(fn (User $u) => $u->is_active ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (User $u) => $u->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (User $u) => $u->update(['is_active' => ! $u->is_active])),

                // Forçar troca de senha
                Action::make('forcarTrocaSenha')
                    ->label('Forçar troca de senha')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(fn (User $u) => $u->update(['must_change_password' => true])),

                EditAction::make()->label('Editar'),

                Action::make('delete')
                    ->label('Excluir')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(fn (User $r) => $r->delete())
                    ->visible(fn ($r) => $r && $r instanceof User && $r->getKey() !== auth()?->user()?->getKey())
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Excluir selecionados')
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
