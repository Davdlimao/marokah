<?php

namespace App\Filament\Resources\Usuarios\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema as LaravelSchema;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Models\Role;

class UsuarioForm
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados pessoais')
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('Nome')->required()->maxLength(255)->columnSpan(6),
                        Forms\Components\TextInput::make('sobrenome')->label('Sobrenome')->maxLength(255)->columnSpan(6),
                        Forms\Components\TextInput::make('telefone')->label('Telefone')->tel()->maxLength(30)->columnSpan(4),
                        Forms\Components\TextInput::make('email')->label('E-mail')->email()->required()->unique(ignoreRecord: true)->columnSpan(8),
                    ]),

                Section::make('Acesso')
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Senha (opcional)')
                            ->password()->revealable()
                            ->dehydrateStateUsing(fn ($s) => filled($s) ? Hash::make($s) : null)
                            ->dehydrated(fn ($s) => filled($s))
                            ->columnSpan(6),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirmar senha')->password()->revealable()
                            ->same('password')->dehydrated(false)->columnSpan(6),

                        // apenas ao criar
                        Forms\Components\Radio::make('modo_senha')
                            ->label('Como entregar a senha?')
                            ->options([
                                'convite'  => 'Enviar link para definir senha',
                                'temp'     => 'Gerar senha temporária (troca obrigatória no 1º login)',
                                'definida' => 'Já defini acima',
                            ])
                            ->default('convite')
                            ->columnSpan(12)
                            ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo')->default(true)->columnSpan(3),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Verificado em')->nullable()->columnSpan(3),

                        Forms\Components\Select::make('roles')
                            ->label('Papéis')
                            ->multiple()->preload()
                            ->relationship('roles','name')
                            ->columnSpan(12),
                    ]),
            ])
            ->columns(12);
    }
}
