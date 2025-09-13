<?php

namespace App\Filament\Clusters\Configuracoes\Pages;

use App\Filament\Clusters\Configuracoes\ConfiguracoesCluster;
use App\Settings\AparenciaSettings;
use App\Settings\ConfiguracoesGerais;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
            Section::make('Marca & Título')->schema([
                TextInput::make('site_name')
                    ->label('Nome do site')
                    ->helperText('Usado no título das abas e como fallback quando não houver logotipo.')
                    ->required()
                    ->dehydrated(true),
            ]),

            Section::make('Identidade Visual')->schema([
                FileUpload::make('logo_header')
                    ->label('Logotipo (cabeçalho – fundo claro)')
                    ->helperText('PNG/SVG. Recom.: 4:1')
                    ->image()
                    ->disk('public')->directory('logos')->dehydrated(true),

                FileUpload::make('logo_header_dark')
                    ->label('Logotipo (cabeçalho – fundo escuro)')
                    ->helperText('Versão para dark/áreas escuras')
                    ->image()
                    ->disk('public')->directory('logos')->dehydrated(true),

                FileUpload::make('logo_quadrado')
                    ->label('Logo quadrado / avatar')
                    ->helperText('1:1 – usado em avatares, ícone interno etc.')
                    ->image()
                    ->disk('public')->directory('logos')->dehydrated(true),

                FileUpload::make('favicon')
                    ->label('Favicon (512×512)')
                    ->image()
                    ->disk('public')->directory('favicons')->dehydrated(true),
            ])->columns(2),

            Section::make('Cor do Tema')->schema([
                ColorPicker::make('cor_primaria')
                    ->label('Cor principal')
                    ->dehydrated(true),
            ]),

            Section::make('Backgrounds')->schema([
                FileUpload::make('bg_login')
                    ->label('Imagem de fundo – Login')
                    ->image()
                    ->disk('public')->directory('backgrounds')->dehydrated(true),

                FileUpload::make('bg_painel')
                    ->label('Imagem de fundo – Painel')
                    ->image()
                    ->disk('public')->directory('backgrounds')->dehydrated(true),
            ])->columns(2),

            Section::make('Avançado')->schema([
                Textarea::make('custom_css_head')
                    ->label('CSS adicional (head)')
                    ->rows(5)->dehydrated(true),
                Textarea::make('custom_js_footer')
                    ->label('JS adicional (footer)')
                    ->rows(5)->dehydrated(true),
            ]),
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Aparência (group=aparencia)
        $row = DB::table('settings')->where('group', 'aparencia')->where('name', 'default')->first();
        $saved = $row ? (array) json_decode($row->payload, true) : [];

        // Nome do site vem de (group=geral)
        $gRow   = DB::table('settings')->where('group', 'geral')->where('name', 'default')->first();
        $geral  = $gRow ? (array) json_decode($gRow->payload, true) : [];
        $saved['site_name'] = $geral['nome_do_site'] ?? 'Marokah';

        // normaliza uploads para string|null
        $uploads = ['logo_header','logo_header_dark','logo_quadrado','favicon','bg_login','bg_painel'];
        foreach ($uploads as $k) {
            if (array_key_exists($k, $saved)) {
                $v = $saved[$k];
                $saved[$k] = is_array($v) ? (count($v) ? (string) $v[0] : null) : ($v === '' ? null : $v);
            }
        }

        return array_replace(AparenciaSettings::defaults(), $data, $saved);
    }

    public function save(): void
    {
        $state = (array) $this->form->getState();

        // separamos o site_name para gravar em "geral"
        $siteName = trim((string) ($state['site_name'] ?? 'Marokah'));
        unset($state['site_name']);

        // salva aparência inteira (group=aparencia)
        $aparencia = array_replace(AparenciaSettings::defaults(), $state);
        DB::table('settings')->updateOrInsert(
            ['group' => 'aparencia', 'name' => 'default'],
            [
                'locked'     => 0,
                'payload'    => json_encode($aparencia, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => Carbon::now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        // atualiza apenas o nome_do_site em "geral" (sem perder outras chaves)
        $gDefaults = ConfiguracoesGerais::defaults();
        $gRow      = DB::table('settings')->where('group','geral')->where('name','default')->first();
        $gPayload  = $gRow ? (array) json_decode($gRow->payload, true) : [];
        $gNew      = array_replace($gDefaults, $gPayload, ['nome_do_site' => $siteName]);

        DB::table('settings')->updateOrInsert(
            ['group' => 'geral', 'name' => 'default'],
            [
                'locked'     => 0,
                'payload'    => json_encode($gNew, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => Carbon::now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        // rehidrata o formulário
        $this->form->fill($this->mutateFormDataBeforeFill([]));

        Notification::make()->title('Configurações de aparência salvas!')->success()->send();
    }
}
