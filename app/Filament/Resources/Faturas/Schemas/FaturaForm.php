<?php

namespace App\Filament\Resources\Faturas\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Assinatura;
use App\Enums\SubscriptionStatus;
use App\Models\Fatura;

class FaturaForm
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da fatura')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Forms\Components\Select::make('empresa_id')
                            ->label('Cliente')
                            ->relationship('empresa', 'nome')
                            ->searchable()->preload()->required()
                            ->reactive()
                            ->afterStateUpdated(function ($set, $get, $state) {
                                // Assinatura ativa mais recente do cliente
                                $assinatura = Assinatura::where('empresa_id', $state)
                                    ->where('status', SubscriptionStatus::ATIVA)
                                    ->latest('started_at')
                                    ->first();

                                $set('assinatura_id', $assinatura?->id);

                                if ($assinatura) {
                                    $valor = $assinatura->valorEfetivo();
                                    $set('subtotal', $valor);
                                    $set('descontos', 0);
                                    $set('acrescimos', 0);
                                    $set('total', $valor);
                                    $set('vencimento', optional($assinatura->nextDueDate())->toDateString());
                                    $set('referencia_ini', now()->startOfMonth()->toDateString());
                                    $set('referencia_fim', now()->endOfMonth()->toDateString());
                                }
                            })
                            ->columnSpan(6),

                            Forms\Components\Select::make('assinatura_id')
                                ->label('Assinatura')
                                ->options(fn ($get) => \App\Models\Assinatura::query()
                                    ->where('empresa_id', $get('empresa_id'))
                                    ->orderByDesc('started_at')
                                    ->get()
                                    ->mapWithKeys(fn ($a) => [
                                        $a->id => sprintf('#%d • %s • R$ %s • %s',
                                            $a->id,
                                            $a->plano?->nome ?? '—',
                                            number_format($a->valorEfetivo(), 2, ',', '.'),
                                            optional($a->started_at)->format('d/m/Y') ?? 'sem início'
                                        ),
                                    ])
                                )
                                ->searchable()->preload()
                                ->rule(function ($get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        if (!$value) return;
                                        $ok = \App\Models\Assinatura::where('id', $value)
                                            ->where('empresa_id', $get('empresa_id'))
                                            ->exists();
                                        if (!$ok) $fail('A assinatura selecionada não pertence ao cliente escolhido.');
                                    };
                                })
                                ->columnSpan(6),
                    ]),

                // ⬇️ Seção recolocada com os campos de valores, datas e referência
                Section::make('Valores e vencimento')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()->minValue(0)->step('0.01')->prefix('R$')
                            ->afterStateHydrated(function ($get, $set, ?Fatura $record) {
                                if ($record) $set('subtotal', (float) $record->subtotal);
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($set, $get) {
                                $s = (float) $get('subtotal');
                                $d = (float) $get('descontos');
                                $a = (float) $get('acrescimos');
                                $set('total', round($s - $d + $a, 2));
                            })
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('descontos')
                            ->label('Descontos')
                            ->numeric()->minValue(0)->step('0.01')->prefix('R$')
                            ->afterStateHydrated(function ($get, $set, ?Fatura $record) {
                                if ($record) $set('descontos', (float) $record->descontos);
                            })
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($set, $get) {
                                $s = (float) $get('subtotal');
                                $d = (float) $get('descontos');
                                $a = (float) $get('acrescimos');
                                $set('total', round($s - $d + $a, 2));
                            })
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('acrescimos')
                            ->label('Acréscimos')
                            ->numeric()->minValue(0)->step('0.01')->prefix('R$')
                            ->afterStateHydrated(function ($get, $set, ?Fatura $record) {
                                if ($record) $set('acrescimos', (float) $record->acrescimos);
                            })
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($set, $get) {
                                $s = (float) $get('subtotal');
                                $d = (float) $get('descontos');
                                $a = (float) $get('acrescimos');
                                $set('total', round($s - $d + $a, 2));
                            })
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->numeric()->step('0.01')->prefix('R$')
                            ->afterStateHydrated(function ($get, $set, ?Fatura $record) {
                                if ($record) $set('total', (float) $record->total);
                            })
                            ->readOnly()
                            ->dehydrated(true)
                            ->columnSpan(3),

                        Forms\Components\DatePicker::make('vencimento')
                            ->label('Data de vencimento')
                            ->required()
                            ->afterStateHydrated(function ($get, $set, ?Fatura $record) {
                                if ($record && $record->vencimento) {
                                    $set('vencimento', $record->vencimento->toDateString());
                                }
                            })
                            ->columnSpan(3),

                        Forms\Components\DatePicker::make('referencia_ini')
                            ->label('Ref. (início)')
                            ->afterStateHydrated(function ($get, $set, ?Fatura $record) {
                                if ($record && $record->referencia_ini) {
                                    $set('referencia_ini', $record->referencia_ini->toDateString());
                                }
                            })
                            ->columnSpan(3),

                        Forms\Components\DatePicker::make('referencia_fim')
                            ->label('Ref. (fim)')
                            ->afterStateHydrated(function ($get, $set, ?Fatura $record) {
                                if ($record && $record->referencia_fim) {
                                    $set('referencia_fim', $record->referencia_fim->toDateString());
                                }
                            })
                            ->columnSpan(3),
                    ]),

                Section::make('Observações')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição / Observações')
                            ->rows(3),
                    ]),
            ])
            ->columns(12);
    }
}
