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
                TextInput::make('nome_do_site')->label('Nome do Site')->required(),
                Toggle::make('site_ativo')->label('Site Ativo')->default(true),
            ]),
            Section::make('Moeda & Logo')->schema([
                FileUpload::make('logotipo_do_site')->label('Logotipo')->image()->directory('logotipos'),
                Select::make('moeda_padrao')->label('Moeda Padrão')->options([
                    'BRL' => 'Real Brasileiro (BRL)',
                    'USD' => 'Dólar Americano (USD)',
                ])->default('BRL')->required(),
            ]),
        ]);
    }
}
