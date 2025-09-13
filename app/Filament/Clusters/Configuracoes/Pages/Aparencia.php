<?php

namespace App\Filament\Clusters\Configuracoes\Pages;

use App\Filament\Clusters\Configuracoes\ConfiguracoesCluster;
use App\Settings\AparenciaSettings;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class Aparencia extends SettingsPage
{
    protected static ?string $cluster = ConfiguracoesCluster::class;
    protected static string $settings = AparenciaSettings::class;

    protected static ?string $title = 'Aparência';
    protected static ?string $navigationLabel = 'Aparência';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identidade Visual')->schema([
                FileUpload::make('logo_header')
                    ->label('Logotipo (cabeçalho)')
                    ->image()
                    ->disk('public')
                    ->directory('logos')
                    ->dehydrated(true),

                FileUpload::make('favicon')
                    ->label('Favicon')
                    ->image()
                    ->disk('public')
                    ->directory('favicons')
                    ->dehydrated(true),

                ColorPicker::make('cor_primaria')
                    ->label('Cor primária')
                    ->dehydrated(true),

                ColorPicker::make('cor_secundaria')
                    ->label('Cor secundária')
                    ->dehydrated(true),

                Toggle::make('tema_escuro_default')
                    ->label('Iniciar no tema escuro')
                    ->default(true)
                    ->dehydrated(true),
            ]),
        ]);
    }

protected function mutateFormDataBeforeFill(array $data): array
{
    // 1) Busca o payload salvo diretamente na tabela settings
    $row = DB::table('settings')
        ->where('group', 'aparencia')
        ->where('name', 'default')
        ->first();

    $saved = [];
    if ($row && isset($row->payload)) {
        $decoded = json_decode($row->payload, true);
        // se por algum motivo estiver duplamente codificado
        $saved = is_array($decoded)
            ? (isset($decoded['logo_header']) || isset($decoded['favicon']) ? $decoded : (json_decode($decoded['payload'] ?? '[]', true) ?: []))
            : [];
    }

    // 2) Normaliza campos de upload para string|null
    foreach (['logo_header', 'favicon'] as $k) {
        if (array_key_exists($k, $saved)) {
            $v = $saved[$k];
            $saved[$k] = is_array($v) ? (count($v) ? (string) $v[0] : null) : ($v === '' ? null : $v);
        }
    }

    // 3) MUITO IMPORTANTE: o que vem do BD deve sobrepor o $data do Filament (que costuma trazer [])
    // defaults < estado do Filament < valores salvos no BD
    return array_replace(AparenciaSettings::defaults(), $data, $saved);
}


    public function save(): void
{
    // 1) pega o estado atual e mescla com defaults
    $data = array_merge(AparenciaSettings::defaults(), (array) $this->form->getState());

    // 2) persiste na tabela `settings`
    DB::table('settings')->updateOrInsert(
        ['group' => 'aparencia', 'name' => 'default'],
        [
            'locked'     => 0,
            'payload'    => json_encode([
                'logo_header'         => $data['logo_header'] ?? null,
                'favicon'             => $data['favicon'] ?? null,
                'cor_primaria'        => $data['cor_primaria'] ?? '#16A34A',
                'cor_secundaria'      => $data['cor_secundaria'] ?? '#0F2B1F',
                'tema_escuro_default' => (bool) ($data['tema_escuro_default'] ?? true),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => Carbon::now(),
            'created_at' => DB::raw('COALESCE(created_at, NOW())'),
        ]
    );

    // 3) re-hidrata lendo do BD (agora com os caminhos finais)
    $fresh = $this->mutateFormDataBeforeFill([]);
    $this->form->fill($fresh);

    Notification::make()
        ->title('Configurações de aparência salvas!')
        ->success()
        ->send();
}

}
