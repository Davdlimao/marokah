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
                FileUpload::make('logo_header')->label('Logo (header)')->image()->directory('logos'),
                FileUpload::make('favicon')->label('Favicon')->image()->directory('favicons'),
                ColorPicker::make('cor_primaria')->label('Cor primária'),
                ColorPicker::make('cor_secundaria')->label('Cor secundária'),
                Toggle::make('tema_escuro_default')->label('Iniciar no tema escuro')->default(true),
            ]),
        ]);
    }
}
