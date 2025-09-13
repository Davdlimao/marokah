<?php

namespace App\Filament\Clusters\Configuracoes\Pages;

use App\Settings\IntegracaoEmailSettings;
use Filament\Forms\Components\{Section, TextInput, Toggle, Checkbox};
use Filament\Pages\Settings\SettingsPage;
use App\Filament\Clusters\Configuracoes\ConfiguracoesCluster;

class IntegracaoEmailSettingsPage extends SettingsPage
{
    protected static string $settings = IntegracaoEmailSettings::class;
    protected static ?string $cluster = ConfiguracoesCluster::class;

    public function getFormSchema(): array
    {
        return [
            Section::make('Campos de teste')
                ->columns(1)
                ->schema([
                    TextInput::make('texto')->label('Campo de Texto')->required(),
                    TextInput::make('numero')->label('Campo Numérico')->numeric(),
                    Toggle::make('toggle')->label('Botão Deslizante'),
                    Checkbox::make('check')->label('Botão de Check'),
                ]),
        ];
    }
}
