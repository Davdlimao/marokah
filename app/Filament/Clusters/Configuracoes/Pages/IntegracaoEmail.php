<?php

namespace App\Filament\Clusters\Configuracoes\Pages;

use App\Filament\Clusters\Configuracoes\ConfiguracoesCluster;
use App\Settings\EmailSettings;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class IntegracaoEmail extends SettingsPage
{
    protected static ?string $cluster = ConfiguracoesCluster::class;
    protected static string $settings = EmailSettings::class;

    protected static ?string $title = 'Integração de E-mail';
    protected static ?string $navigationLabel = 'Integração de E-mail';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    /** Opcional: restringir acesso apenas a superadmin (ajuste p/ seu gate/política). */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) return false;

        // se usa spatie/permission:
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('superadmin');
        }

        // fallback simples:
        return (bool) ($user->is_admin ?? false);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Remetente')->schema([
                Grid::make(2)->schema([
                    TextInput::make('from_address')->label('E-mail do remetente')->email()->dehydrated(true),
                    TextInput::make('from_name')->label('Nome do remetente')->dehydrated(true),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('reply_to')->label('Responder para (opcional)')->email()->dehydrated(true),
                    TextInput::make('bcc')->label('Cópia oculta (BCC, opcional, separar por vírgula)')->dehydrated(true),
                ]),
            ]),

            Section::make('SMTP')->schema([
                TextInput::make('host')
                    ->label('Host')
                    ->placeholder('smtp.seuprovedor.com')
                    ->required()
                    ->dehydrated(true),

                Grid::make(3)->schema([
                    TextInput::make('port')
                        ->label('Porta')
                        ->numeric()
                        ->default(587)
                        ->required()
                        ->dehydrated(true),

                    Select::make('encryption')
                        ->label('Criptografia')
                        ->options([
                            'tls' => 'TLS',
                            'ssl' => 'SSL',
                            'none' => 'Sem criptografia',
                        ])
                        ->default('tls')
                        ->dehydrated(true),

                    TextInput::make('username')
                        ->label('Usuário')
                        ->dehydrated(true),
                ]),

                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    ->dehydrated(true)
                    // se vier vazio, não sobrescreve o valor salvo
                    ->dehydrateStateUsing(fn ($state) => $state === '' ? null : $state),
            ]),
        ]);
    }

    /** ========= Segurança: helpers p/ criptografia dos segredos ========= */

    /** Quais chaves serão criptografadas ao salvar. */
    private const SECRET_KEYS = ['password'];

    /** Criptografa segredos não criptografados. */
    private function encryptSecrets(array $data): array
    {
        foreach (self::SECRET_KEYS as $key) {
            if (!array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
                continue;
            }
            if (is_string($data[$key]) && Str::startsWith($data[$key], 'enc:')) {
                continue; // já criptografado
            }
            $data[$key] = 'enc:' . base64_encode(Crypt::encryptString($data[$key]));
        }
        return $data;
    }

    /** Descriptografa segredos vindos do banco para uso interno. */
    private function decryptSecrets(array $data): array
    {
        foreach (self::SECRET_KEYS as $key) {
            if (!isset($data[$key]) || !is_string($data[$key])) {
                continue;
            }
            if (Str::startsWith($data[$key], 'enc:')) {
                try {
                    $data[$key] = Crypt::decryptString(
                        base64_decode(Str::after($data[$key], 'enc:'))
                    );
                } catch (\Throwable) {
                    $data[$key] = null; // APP_KEY trocado/dado corrompido
                }
            }
        }
        return $data;
    }

    /** Carrega do BD (BD vence), descriptografando segredos. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $row   = DB::table('settings')->where('group','email')->where('name','default')->first();
        $saved = $row ? (array) json_decode($row->payload, true) : [];

        if (isset($saved['port'])) $saved['port'] = (int) $saved['port'];
        if (($saved['encryption'] ?? null) === 'none') $saved['encryption'] = null;

        // 🔐 segredos descriptografados para uso interno (não preencher senha na UI!)
        $saved = $this->decryptSecrets($saved);
        unset($saved['password']);

        return array_replace(EmailSettings::defaults(), $data, $saved);
    }

    public function save(): void
    {
        // estado atual
        $incoming = (array) $this->form->getState();

        // mantém senha atual se input veio vazio
        $currentRow = DB::table('settings')->where('group','email')->where('name','default')->first();
        $currentRaw = $currentRow ? (array) json_decode($currentRow->payload, true) : [];
        $current    = $this->decryptSecrets($currentRaw);
        if (($incoming['password'] ?? null) === null && !empty($current['password'])) {
            $incoming['password'] = $current['password'];
        }

        // normaliza
        $data = array_replace(EmailSettings::defaults(), Arr::except($incoming, []));
        $data['port'] = (int) $data['port'];
        if (($data['encryption'] ?? null) === 'none') $data['encryption'] = null;

        // 🔐 criptografa antes de gravar
        $toStore = $this->encryptSecrets($data);

        DB::table('settings')->updateOrInsert(
            ['group' => 'email', 'name' => 'default'],
            [
                'locked'     => 0,
                'payload'    => json_encode($toStore, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => Carbon::now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        // aplica em runtime usando dados limpos (descriptografados)
        $this->applyRuntimeMailConfig($data);

        // re-hidrata
        $this->form->fill($this->mutateFormDataBeforeFill([]));

        Notification::make()->title('Configurações de e-mail salvas!')->success()->send();
    }

    /** Aplica as configs em runtime (sem .env). */
    protected function applyRuntimeMailConfig(array $cfg): void
    {
        // força SMTP como padrão
        config(['mail.default' => 'smtp']);

        // remetente
        if (!empty($cfg['from_address'])) {
            config(['mail.from.address' => $cfg['from_address']]);
            config(['mail.from.name'    => $cfg['from_name'] ?? config('app.name')]);
        }

        // produção: se por algum motivo vier sem criptografia, força TLS
        if (app()->isProduction() && empty($cfg['encryption'])) {
            $cfg['encryption'] = 'tls';
        }

        // mailer SMTP
        config(['mail.mailers.smtp' => array_filter([
            'transport'  => 'smtp',
            'host'       => $cfg['host'] ?: null,
            'port'       => $cfg['port'] ?? 587,
            'encryption' => $cfg['encryption'] ?? null,
            'username'   => $cfg['username'] ?? null,
            'password'   => $cfg['password'] ?? null,
            'timeout'    => null,
            'auth_mode'  => null,
        ], fn ($v) => $v !== null)]);

        // Reply-To / BCC globais
        $mailer = app('mailer');
        if (!empty($cfg['reply_to'])) {
            $mailer->alwaysReplyTo($cfg['reply_to']);
        }
        if (!empty($cfg['bcc'])) {
            collect(explode(',', $cfg['bcc']))->map(fn($e) => trim($e))->filter()->each(
                fn($b) => $mailer->alwaysBcc($b)
            );
        }
    }

    /** Botão "Enviar e-mail de teste" com rate-limit. */
    public function getFormActions(): array
    {
        return [
            Actions\Action::make('test')
                ->label('Enviar e-mail de teste')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->form([
                    TextInput::make('para')->label('Enviar para')->email()->required(),
                ])
                ->action(function (array $data) {
                    // rate-limit: 3 tentativas por minuto por usuário
                    $key = 'mailtest:'.auth()->id();
                    if (RateLimiter::tooManyAttempts($key, 3)) {
                        Notification::make()
                            ->title('Aguarde um pouco antes de enviar outro teste.')
                            ->danger()
                            ->send();
                        return;
                    }
                    RateLimiter::hit($key, 60);

                    // estado atual + senha persistida se input estiver vazio
                    $state = array_replace(EmailSettings::defaults(), (array) $this->form->getState());
                    $row   = DB::table('settings')->where('group','email')->where('name','default')->first();
                    $cur   = $row ? (array) json_decode($row->payload, true) : [];
                    $cur   = $this->decryptSecrets($cur);

                    if (($state['password'] ?? null) === null && !empty($cur['password'])) {
                        $state['password'] = $cur['password'];
                    }

                    $this->applyRuntimeMailConfig($state);

                    try {
                        $subject = 'Teste de e-mail - ' . (config('mail.from.name') ?: config('app.name'));
                        Mail::raw(
                            "Tudo certo! Este é um e-mail de teste do Marokah.\n\nData: " . now(),
                            function ($m) use ($data, $subject) {
                                $m->to($data['para'])->subject($subject);
                            }
                        );

                        Notification::make()->title('E-mail de teste enviado!')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Falha ao enviar o e-mail de teste')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            $this->getSaveFormAction(),
        ];
    }
}
