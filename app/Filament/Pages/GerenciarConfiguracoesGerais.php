<?php

namespace App\Filament\Pages;

use App\Settings\ConfiguracoesGerais;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use UnitEnum;
use BackedEnum;

class GerenciarConfiguracoesGerais extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = ConfiguracoesGerais::class;
    
    protected static ?string $title = 'Configurações Gerais';

    protected static ?string $navigationLabel = 'Configurações Gerais';
    
    // Opcional: para agrupar no menu
    protected static string|UnitEnum|null $navigationGroup = 'Administração';

    public function getFormSchema(): array
    {
        return [
            Section::make('Preferências do Site')
                ->description('Defina as configurações gerais do sistema.')
                ->schema([
                    TextInput::make('nome_do_site')
                        ->label('Nome do Site')
                        ->required(),
                        Toggle::make('site_ativo')
                            ->label('Site Ativo')
                            ->default(true)
                            ->helperText('Desative para colocar o sistema em modo de manutenção.'),
                    ]),

                Section::make('Aparência')
                    ->description('Personalize a identidade visual do sistema.')
                    ->schema([
                        FileUpload::make('logotipo_do_site')
                            ->label('Logotipo do Site')
                            ->image()
                            ->directory('logotipos'),
                        Select::make('moeda_padrao')
                            ->label('Moeda Padrão')
                            ->options([
                                'BRL' => 'Real Brasileiro (BRL)',
                                'USD' => 'Dólar Americano (USD)',
                            ])
                            ->default('BRL')
                            ->required(),
                    ]),
            ];
    }
}