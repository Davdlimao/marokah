<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuariosResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Models\Convite;
use App\Mail\ConviteUsuarioMail;
use App\Support\EmailRuntime;

class ListUsuarios extends ListRecords
{
    protected static string $resource = UsuariosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('convidar')
                ->label('Convidar usuário')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->form([
                    Forms\Components\TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required()
                        ->rule('unique:users,email'),
                    Forms\Components\TextInput::make('nome')
                        ->label('Nome')
                        ->maxLength(100),
                    Forms\Components\DateTimePicker::make('expira_em')
                        ->label('Expira em')
                        ->minDate(now())
                        ->default(now()->addDays(7)),
                ])
                ->action(function (array $data, Actions\Action $action) {
                    // aplica as configs salvas (driver, host, porta, from, etc.)
                    \App\Support\EmailRuntime::applyFromDb();

                    // Garante que o 'from' está setado na config
                    $fromAddress = config('mail.from.address');
                    $fromName = config('mail.from.name');
                    if (empty($fromAddress)) {
                        config(['mail.from.address' => 'no-reply@' . parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost']);
                    }
                    if (empty($fromName)) {
                        config(['mail.from.name' => config('app.name', 'Marokah')]);
                    }

                    // token plano + hash guardado
                    $tokenPlano = Str::random(64);
                    $hash       = hash('sha256', $tokenPlano);

                    $convite = Convite::create([
                        'email'            => $data['email'],
                        'nome'             => $data['nome'] ?? null,
                        'papeis'           => null, // ajuste se for usar papéis
                        'token_hash'       => $hash,
                        'expira_em'        => $data['expira_em'] ?? now()->addDays(7),
                        'convidado_por_id' => auth()->id(),
                    ]);

                    // link assinado com expiração
                    $url = route('convites.mostrar', [
                        'convite' => $convite->id,
                        'token'   => $tokenPlano,
                    ]);

                    try {
                        // envio síncrono (sem fila)
                        Mail::to($convite->email)->send(new ConviteUsuarioMail($convite, $url));

                        Notification::make()
                            ->title('Convite enviado!')
                            ->body("Um convite foi enviado para **{$convite->email}**.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        $action->failure();

                        Notification::make()
                            ->title('Falha ao enviar o convite')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }

                    Mail::to($convite->email)->send(new \App\Mail\ConviteUsuarioMail($convite, $url));
                }),

            // Se quiser impedir criação manual de usuários:
            // Actions\CreateAction::make()->hidden(),
        ];
    }
}
