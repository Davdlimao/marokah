<?php

namespace App\Filament\Clusters\Configuracoes\Pages;

use App\Filament\Clusters\Configuracoes\ConfiguracoesCluster;
use App\Settings\EmailSettings;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class IntegracaoEmail extends SettingsPage
{
    protected static ?string $cluster = ConfiguracoesCluster::class;
    protected static string $settings = EmailSettings::class;

    protected static ?string $title = 'Integração de E-mail';
    protected static ?string $navigationLabel = 'Integração de E-mail';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('SMTP')->schema([
                Select::make('driver')->label('Driver')->options([
                    'smtp'    => 'SMTP',
                    'ses'     => 'Amazon SES',
                    'mailgun' => 'Mailgun',
                ])->required()->default('smtp')->dehydrated(true),

                TextInput::make('host')->label('Host')->placeholder('smtp.seuprovedor.com')->required()->dehydrated(true),
                TextInput::make('port')->label('Porta')->numeric()->default(587)->dehydrated(true),
                TextInput::make('username')->label('Usuário')->dehydrated(true),
                TextInput::make('password')->label('Senha')->password()->revealable()->dehydrated(true),
                Toggle::make('tls')->label('Usar TLS')->default(true)->dehydrated(true),

                TextInput::make('from_address')->label('Remetente (from)')->email()->dehydrated(true),
                TextInput::make('from_name')->label('Nome do Remetente')->dehydrated(true),
            ]),
        ]);
    }

    /**
     * Carrega do BD (settings.group='email', name='default') e dá prioridade ao que está salvo.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $row = DB::table('settings')
            ->where('group', 'email')
            ->where('name', 'default')
            ->first();

        $saved = $row ? (array) json_decode($row->payload, true) : [];

        // normalizações
        if (isset($saved['port'])) {
            $saved['port'] = (int) $saved['port'];
        }
        if (isset($saved['tls'])) {
            $saved['tls'] = (bool) $saved['tls'];
        }

        // defaults < estado do Filament < valores salvos no BD (BD vence)
        return array_replace(EmailSettings::defaults(), $data, $saved);
    }

    public function save(): void
    {
        $data = array_replace(
            EmailSettings::defaults(),
            (array) $this->form->getState()
        );

        DB::table('settings')->updateOrInsert(
            ['group' => 'email', 'name' => 'default'],
            [
                'locked'     => 0,
                'payload'    => json_encode([
                    'driver'       => $data['driver'] ?? 'smtp',
                    'host'         => $data['host'] ?? '',
                    'port'         => (int) ($data['port'] ?? 587),
                    'username'     => $data['username'] ?? null,
                    'password'     => $data['password'] ?? null,
                    'tls'          => (bool) ($data['tls'] ?? true),
                    'from_address' => $data['from_address'] ?? null,
                    'from_name'    => $data['from_name'] ?? null,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => Carbon::now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        // re-hidrata com o que ficou no BD (garante exibição imediata)
        $this->form->fill($this->mutateFormDataBeforeFill([]));

        Notification::make()
            ->title('Configurações de e-mail salvas!')
            ->success()
            ->send();
    }
}
