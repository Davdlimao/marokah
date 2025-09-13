<?php

namespace App\Filament\Resources\Assinaturas\Schemas;

use App\Models\Plano;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssinaturaForm
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da assinatura')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Forms\Components\Select::make('empresa_id')
                            ->label('Cliente')
                            ->relationship('empresa', 'nome')
                            ->searchable()->preload()
                            ->required()
                            ->dehydrated(true)
                            ->afterStateUpdated(function ($set, $state) {
                                // se o cliente tem dia padrão, use; senão, mantenha como está
                                $dia = \App\Models\Cliente::find($state)?->dia_vencimento;
                                if ($dia) $set('dia_vencimento', (int) $dia);
                            })
                            ->columnSpan(6),

                        Forms\Components\Select::make('plano_id')
                            ->label('Plano')
                            ->options(Plano::query()->orderBy('nome')->pluck('nome', 'id'))
                            ->searchable()->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($set, $state) {
                                $plano = Plano::find($state);
                                if ($plano) {
                                    $set('valor', $plano->valor);
                                    $set('periodicidade', $plano->periodicidade instanceof \BackedEnum ? $plano->periodicidade->value : strtoupper((string)$plano->periodicidade));
                                }
                            })
                            ->columnSpan(6),

                        Forms\Components\TextInput::make('valor')
                            ->label('Valor da assinatura')
                            ->numeric()->prefix('R$')->step('0.01')
                            ->helperText('Se vazio, usa o valor do plano.')
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('periodicidade')
                            ->label('Periodicidade')
                            ->disabled()->dehydrated(true)
                            ->columnSpan(3),

                        Forms\Components\Select::make('status')
                            ->label('Status da assinatura')
                            ->native(false)
                            ->options([
                                'ATIVA' => 'Ativa',
                                'PAUSADA' => 'Pausada',
                                'INADIMPLENTE' => 'Inadimplente',
                                'CANCELADA' => 'Cancelada',
                            ])
                            ->default('ATIVA')
                            ->columnSpan(3),

                        Forms\Components\DatePicker::make('started_at')
                            ->label('Início de uso')
                            ->reactive()
                            ->afterStateUpdated(function ($set, $state, $get) {
                                // se não houver dia definido, usa o dia do started_at
                                if (! $get('dia_vencimento') && $state) {
                                    $set('dia_vencimento', \Carbon\Carbon::parse($state)->day);
                                }
                            })
                            ->columnSpan(3),

                        Forms\Components\DatePicker::make('trial_ends_at')
                            ->label('Trial até')
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('dia_vencimento')
                            ->label('Dia de vencimento mensal')
                            ->numeric()->minValue(1)->maxValue(31)
                            ->helperText('Usado para gerar as cobranças recorrentes.')
                            ->columnSpan(3),
                    ]),

                Section::make('Observações')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Forms\Components\Textarea::make('obs')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpan(12),
                    ]),
            ])
            ->columns(12);
    }
}
