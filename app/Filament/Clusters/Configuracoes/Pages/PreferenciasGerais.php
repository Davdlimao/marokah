<?php

namespace App\Filament\Clusters\Configuracoes\Pages;

use App\Filament\Clusters\Configuracoes\ConfiguracoesCluster;
use App\Settings\ConfiguracoesGerais;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PreferenciasGerais extends SettingsPage
{
    protected static ?string $cluster = ConfiguracoesCluster::class;
    protected static string $settings = ConfiguracoesGerais::class;

    protected static ?string $title = 'Preferências Gerais';
    protected static ?string $navigationLabel = 'Preferências Gerais';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-vertical';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Preferências do Site')->schema([
                TextInput::make('nome_do_site')
                    ->label('Nome do Site')
                    ->required()
                    ->dehydrated(true),

                Toggle::make('site_ativo')
                    ->label('Site Ativo')
                    ->default(true)
                    ->dehydrated(true),
            ]),

            Section::make('Moeda & Logo')->schema([
                FileUpload::make('logotipo_do_site')
                    ->label('Logotipo')
                    ->image()
                    ->disk('public')
                    ->directory('logotipos')
                    ->dehydrated(true),

                Select::make('moeda_padrao')
                    ->label('Moeda Padrão')
                    ->options([
                        'BRL' => 'Real Brasileiro (BRL)',
                        'USD' => 'Dólar Americano (USD)',
                    ])
                    ->default('BRL')
                    ->required()
                    ->dehydrated(true),
            ]),
        ]);
    }

    /**
     * Lê diretamente da tabela `settings` (group=geral, name=default)
     * e dá precedência ao que está salvo no BD.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $row = DB::table('settings')
            ->where('group', 'geral')
            ->where('name', 'default')
            ->first();

        $saved = $row ? (array) json_decode($row->payload, true) : [];

        // normaliza o upload para string|null (o FileUpload não aceita array)
        if (array_key_exists('logotipo_do_site', $saved)) {
            $v = $saved['logotipo_do_site'];
            $saved['logotipo_do_site'] =
                is_array($v) ? (count($v) ? (string) $v[0] : null) : ($v === '' ? null : $v);
        }

        // defaults < estado do Filament < valores salvos no BD   (BD vence)
        return array_replace(ConfiguracoesGerais::defaults(), $data, $saved);
    }

    public function save(): void
    {
        $data = array_replace(
            ConfiguracoesGerais::defaults(),
            (array) $this->form->getState()
        );

        DB::table('settings')->updateOrInsert(
            ['group' => 'geral', 'name' => 'default'],
            [
                'locked'  => 0,
                'payload' => json_encode([
                    'nome_do_site'     => $data['nome_do_site'] ?? 'Marokah',
                    'site_ativo'       => (bool) ($data['site_ativo'] ?? true),
                    'logotipo_do_site' => $data['logotipo_do_site'] ?? null,
                    'moeda_padrao'     => $data['moeda_padrao'] ?? 'BRL',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => Carbon::now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        // re-hidrata com o que ficou no BD (mostra o logo salvo e demais valores)
        $this->form->fill($this->mutateFormDataBeforeFill([]));

        Notification::make()
            ->title('Preferências gerais salvas!')
            ->success()
            ->send();
    }
}
