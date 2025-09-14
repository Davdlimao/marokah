<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuariosResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Forms;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use App\Models\Convite;
use App\Mail\ConviteUsuarioMail;
use App\Support\EmailRuntime;

class ListUsuarios extends ListRecords
{
    protected static string $resource = UsuariosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo usuário'),

            Actions\Action::make('convidar')
                ->label('Convidar usuário')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->form([
                    Forms\Components\TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required(),

                    Forms\Components\TextInput::make('nome')
                        ->label('Nome'),

                    Forms\Components\Select::make('papeis')
                        ->label('Papéis a atribuir')
                        ->multiple()
                        ->options(function () {
                            // lista de papéis do guard web
                            return Role::query()
                                ->where('guard_name', 'web')
                                ->orderBy('name')
                                ->pluck('name', 'name')
                                ->all();
                        })
                        ->helperText('Opcional — os papéis serão aplicados ao criar a conta.'),

                    Forms\Components\DateTimePicker::make('expira_em')
                        ->label('Expira em')
                        ->minDate(now())
                        ->default(now()->addDays(7)),
                ])
                // MUITO IMPORTANTE: o callback deve receber array $data
                ->action(function (array $data) {
                    // aplica e-mail runtime, se existir utilitário
                    if (class_exists(\App\Support\EmailRuntime::class)) {
                        \App\Support\EmailRuntime::applyFromDb();
                    }

                    // gera token plano e hash que será validado na rota assinada
                    $tokenPlano = Str::random(64);
                    $hash       = hash('sha256', $tokenPlano);

                    $convite = Convite::create([
                        'email'            => $data['email'],
                        'nome'             => $data['nome'] ?? null,
                        'papeis'           => $data['papeis'] ?? null, // json
                        'token_hash'       => $hash,
                        'expira_em'        => $data['expira_em'] ?? now()->addDays(7),
                        'convidado_por_id' => auth()?->id(),
                    ]);

                    // link assinado com validade
                    $url = URL::temporarySignedRoute(
                        'convites.mostrar',
                        $convite->expira_em ?? now()->addDays(7),
                        ['convite' => $convite->id, 'token' => $tokenPlano]
                    );

                    // envia o e-mail
                    Mail::to($convite->email)->send(new ConviteUsuarioMail($convite, $url));

                    \Filament\Notifications\Notification::make()
                        ->title('Convite enviado!')
                        ->body("Um convite foi enviado para **{$convite->email}**.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
