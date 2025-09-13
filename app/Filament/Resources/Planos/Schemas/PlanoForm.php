<?php

namespace App\Filament\Resources\Planos\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms;
use Illuminate\Validation\Rules\Unique;

class PlanoForm
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações do plano')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome do plano')
                            ->required()
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, $get) =>
                                    $rule->where('periodicidade', strtoupper((string) $get('periodicidade')))
                            )
                            ->columnSpan(6),

                        Forms\Components\Select::make('periodicidade')
                            ->label('Periodicidade')
                            ->options([
                                'MENSAL'     => 'Mensal',
                                'TRIMESTRAL' => 'Trimestral',
                                'SEMESTRAL'  => 'Semestral',
                                'ANUAL'      => 'Anual',
                            ])
                            ->default('MENSAL')
                            ->required()
                            ->native(false)
                            ->columnSpan(3)
                            // Normaliza em uppercase ao salvar
                            ->dehydrateStateUsing(fn ($state) => strtoupper((string) $state)),

                        Forms\Components\TextInput::make('valor')
                            ->label('Valor do plano')
                            ->numeric()
                            ->prefix('R$')
                            ->minValue(0)
                            ->step('0.01')
                            ->required()
                            ->helperText('Valor da mensalidade ou recorrência do plano.')
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('taxa_adesao')
                            ->label('Taxa de adesão')
                            ->numeric()
                            ->prefix('R$')
                            ->step('0.01')
                            ->helperText('Taxa cobrada apenas na adesão (opcional).')
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('trial_dias')
                            ->label('Dias de teste grátis')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(60)
                            ->helperText('Período de teste gratuito (0 para não oferecer).')
                            ->columnSpan(3),

                        Forms\Components\Select::make('status')
                            ->label('Status do plano')
                            ->options(['ATIVO' => 'Ativo', 'INATIVO' => 'Inativo'])
                            ->default('ATIVO')
                            ->native(false)
                            ->columnSpan(3),

                        Forms\Components\Toggle::make('oculto')
                            ->label('Ocultar na listagem pública?')
                            ->helperText('Se marcado, o plano não aparece para novos clientes.')
                            ->columnSpan(3),

                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição detalhada')
                            ->rows(4)
                            ->helperText('Descrição longa, benefícios, observações, etc.')
                            ->columnSpan(12),
                    ]),

                Section::make('Recursos e limites')
                    ->columnSpanFull()
                    ->columns(12)
                    ->collapsible()
                    ->schema([
                        Forms\Components\KeyValue::make('recursos')
                            ->label('Recursos (chave → descrição curta)')
                            ->addButtonLabel('Adicionar recurso')
                            ->columnSpan(6),

                        Forms\Components\KeyValue::make('limites')
                            ->label('Limites (chave → número)')
                            ->addButtonLabel('Adicionar limite')
                            ->columnSpan(6),
                    ]),
            ])
            ->columns(12);
    }
}
