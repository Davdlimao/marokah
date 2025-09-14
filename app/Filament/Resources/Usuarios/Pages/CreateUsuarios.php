<?php

namespace App\Filament\Resources\Usuarios\Pages;

use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Models\Convite;
use App\Mail\ConviteUsuarioMail;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Usuarios\UsuariosResource;

class CreateUsuarios extends CreateRecord
{
    protected static string $resource = UsuariosResource::class;

    private string $modoSenha = 'convite';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->modoSenha = $data['modo_senha'] ?? 'convite';
        unset($data['modo_senha']); // não vai para a tabela
        return $data;
    }

    protected function afterCreate(): void
    {
        if (class_exists(\App\Support\EmailRuntime::class) && method_exists(\App\Support\EmailRuntime::class, 'applyFromSettings')) {
            \App\Support\EmailRuntime::applyFromSettings();
        }

        $usuario = $this->getRecord();

        $usuario = $this->record;

        if ($this->modoSenha === 'convite') {
            // usa o mesmo fluxo do convite
            $tokenPlano = Str::random(64);
            $convite = Convite::create([
                'email'      => $usuario->email,
                'nome'       => trim($usuario->name.' '.$usuario->sobrenome),
                'papeis'     => $usuario->getRoleNames()->values()->all(),
                'convidado_por_id' => auth()?->user()?->id,
                'expira_em'  => now()->addDays(7),
                'convidado_por_id' => auth()->id(),
            ]);

            $url = URL::temporarySignedRoute(
                'convites.mostrar',
                $convite->expira_em,
                ['convite' => $convite->id, 'token' => $tokenPlano],
            );

            Mail::to($usuario->email)->send(new ConviteUsuarioMail($convite, $url));

            Notification::make()->title('Link enviado ao usuário')->success()->send();
        }

        if ($this->modoSenha === 'temp') {
            $senha = Str::password(12);
            $usuario->forceFill([
                'password' => bcrypt($senha),
                'must_change_password' => true,
            ])->save();

            // você pode usar um Mailable simples para comunicar a senha provisória
            Mail::to($usuario->email)->send(new \App\Mail\SenhaTemporariaMail(
                nome: $usuario->name,
                senha: $senha,
            ));

            Notification::make()->title('Senha provisória enviada')->success()->send();
        }
    }
}
