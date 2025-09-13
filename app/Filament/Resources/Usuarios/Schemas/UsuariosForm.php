<?php

namespace App\Filament\Resources\Usuarios\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema as LaravelSchema;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

class UsuarioForm
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do usuário')
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                                return $rule;
                            })
                            ->columnSpan(6),

                        // senha só grava se preenchida (não sobrescreve vazio no edit)
                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                                ->rule(Password::default())
                                ->validationMessages([
                                    'min' => 'A senha deve ter no mínimo :min caracteres.',
                                    'required' => 'Informe uma senha.',
                                    'confirmed' => 'A confirmação da senha não confere.',
                                ])
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->columnSpan(6),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirmar senha')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->dehydrated(false)
                            ->columnSpan(6),

                        // Campo opcional (só use se tiver adicionado a coluna na migração abaixo)
                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo')
                            ->visible(fn () => LaravelSchema::hasColumn('users', 'is_active'))
                            ->columnSpan(3),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Verificado em')
                            ->helperText('Defina para marcar e-mail como verificado.')
                            ->nullable()
                            ->columnSpan(3),
                    ]),
            ])
            ->columns(12);
    }
}
