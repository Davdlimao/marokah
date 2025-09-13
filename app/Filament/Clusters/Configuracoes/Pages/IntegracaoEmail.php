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
                    'smtp'     => 'SMTP',
                    'ses'      => 'Amazon SES',
                    'mailgun'  => 'Mailgun',
                ])->required()->default('smtp'),

                TextInput::make('host')->label('Host')->placeholder('smtp.seuprovedor.com')->required(),
                TextInput::make('port')->label('Porta')->numeric()->default(587),
                TextInput::make('username')->label('Usuário'),
                TextInput::make('password')->label('Senha')->password()->revealable(),
                Toggle::make('tls')->label('Usar TLS')->default(true),

                TextInput::make('from_address')->label('Remetente (from)')->email(),
                TextInput::make('from_name')->label('Nome do Remetente'),
            ]),
        ]);
    }
}
