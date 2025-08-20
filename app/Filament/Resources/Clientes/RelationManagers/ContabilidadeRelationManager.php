<?php

namespace App\Filament\Resources\Clientes\RelationManagers;

use App\Support\BrDocuments;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Closure;

class ContabilidadeRelationManager extends RelationManager
{
    protected static string $relationship = 'contabilidades';
    protected static ?string $title = 'Contabilidade';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('razao_social')
                ->label('Nome do escritório')
                ->maxLength(255)
                ->columnSpan(6),

            // flags visuais
            Hidden::make('cnpj_ok')->dehydrated(false)->reactive(),
            Hidden::make('cnpj_hint')->dehydrated(false)->reactive(),
            Hidden::make('email_ok')->dehydrated(false)->reactive(),
            Hidden::make('email_hint')->dehydrated(false)->reactive(),
            Hidden::make('tel_ok')->dehydrated(false)->reactive(),
            Hidden::make('tel_hint')->dehydrated(false)->reactive(),

            TextInput::make('cnpj')
                ->label('CNPJ')
                ->mask('99.999.999/9999-99')
                ->live(onBlur: true)
                ->rules(fn (): array => [
                    function (string $attribute, $value, \Closure $fail) {
                        $d = preg_replace('/\D+/', '', (string) $value);
                        if ($d && ! \App\Support\BrDocuments::cnpj($d)) {
                            $fail('CNPJ inválido.');
                        }
                    },
                ])
                ->suffixIcon(fn ($get) => match ($get('cnpj_ok')) {
                    true => 'heroicon-m-check-circle',
                    false => 'heroicon-m-x-circle',
                    default => null,
                })
                ->suffixIconColor(fn ($get) => $get('cnpj_ok') ? 'success' : 'danger')
                ->hint(fn ($get) => $get('cnpj_hint'))
                ->hintColor(fn ($get) => $get('cnpj_ok') ? 'success' : 'danger')
                ->afterStateUpdated(function ($set, $get, ?string $state) {
                    $d = preg_replace('/\D+/', '', (string) $state);
                    if (!$d) { $set('cnpj_ok', null); $set('cnpj_hint', null); return; }
                    $ok = \App\Support\BrDocuments::cnpj($d);
                    $set('cnpj_ok', $ok);
                    $set('cnpj_hint', $ok ? 'CNPJ válido.' : 'CNPJ inválido.');
                })
                ->columnSpan(4),

            TextInput::make('nome_contato')
                ->label('Nome do contador responsável')
                ->required()
                ->maxLength(255)
                ->columnSpan(6),

            TextInput::make('email')
                ->label('E-mail')->email()->required()
                ->live(onBlur: true)
                ->suffixIcon(fn ($get) => match ($get('email_ok')) {
                    true => 'heroicon-m-check-circle',
                    false => 'heroicon-m-x-circle',
                    default => null,
                })
                ->suffixIconColor(fn ($get) => $get('email_ok') ? 'success' : 'danger')
                ->hint(fn ($get) => $get('email_hint'))
                ->hintColor(fn ($get) => $get('email_ok') ? 'success' : 'danger')
                ->afterStateUpdated(function ($set, $get, ?string $state) {
                    if ($state === null || $state === '') { $set('email_ok', null); $set('email_hint', null); return; }
                    $ok = filter_var($state, FILTER_VALIDATE_EMAIL) !== false;
                    $set('email_ok', $ok);
                    $set('email_hint', $ok ? 'E-mail válido.' : 'E-mail inválido.');
                })
                ->columnSpan(5),

            TextInput::make('telefone')
                ->label('Telefone')
                // dica: para evitar o “[ ]” no fim, use UMA máscara fixa (celular)
                // ou deixe sem máscara e só valide; aqui vou manter celular padrão:
                ->mask('(99) 99999-9999')
                ->live(onBlur: true)
                ->rules(['nullable', 'regex:/^\D*\d{2}\D*\d{4,5}\D*\d{4}\D*$/'])
                ->suffixIcon(fn ($get) => match ($get('tel_ok')) {
                    true => 'heroicon-m-check-circle',
                    false => 'heroicon-m-x-circle',
                    default => null,
                })
                ->suffixIconColor(fn ($get) => $get('tel_ok') ? 'success' : 'danger')
                ->hint(fn ($get) => $get('tel_hint'))
                ->hintColor(fn ($get) => $get('tel_ok') ? 'success' : 'danger')
                ->afterStateUpdated(function ($set, $get, ?string $state) {
                    if (!$state) { $set('tel_ok', null); $set('tel_hint', null); return; }
                    $d = preg_replace('/\D+/', '', (string) $state);
                    $ok = preg_match('/^\d{10,11}$/', $d) === 1;
                    $set('tel_ok', $ok);
                    $set('tel_hint', $ok ? 'Telefone válido.' : 'Telefone inválido.');
                })
                ->columnSpan(3),

            Toggle::make('principal')->label('Principal?')->inline(false)->columnSpan(3),

            Textarea::make('observacoes')->label('Observações')->rows(3)->columnSpan(12),
        ])->columns(12);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('principal', 'desc')
            ->columns([
                TextColumn::make('nome_contato')->label('Nome do responsável')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->copyable()->toggleable(),
                TextColumn::make('telefone')
                    ->label('Telefone')
                    ->formatStateUsing(function ($state) {
                        $d = preg_replace('/\D+/', '', (string) $state);
                        if ($d === '') return '—';
                        return strlen($d) === 11
                            ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $d)
                            : (strlen($d) === 10 ? preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $d) : '—');
                    }),
                TextColumn::make('razao_social')->label('Nome do escritório')->toggleable(),
                TextColumn::make('principal_label')
                    ->label('Principal')
                    ->state(fn ($record) => $record->principal ? 'Principal' : 'Não')
                    ->badge()
                    ->color(fn ($record) => $record->principal ? 'success' : 'warning'),
            ])
            ->headerActions([
                CreateAction::make()->label('Nova contabilidade'),
            ])
            ->recordActions([
            EditAction::make()->label('Editar'),
            Action::make('tornarPrincipal')
                ->label('Definir como principal')
                ->icon('heroicon-o-star')
                ->color('warning')
                ->visible(fn (\App\Models\Contabilidade $record) => ! $record->principal)
                ->requiresConfirmation()
                ->action(function (\App\Models\Contabilidade $record) {
                    \App\Models\Contabilidade::where('empresa_id', $record->empresa_id)->update(['principal' => false]);
                    $record->update(['principal' => true]);
                })
                ->successNotificationTitle('Contabilidade marcada como principal'),
            DeleteAction::make()->label('Excluir'),
        ])
        ->groupedBulkActions([
            DeleteBulkAction::make(),
        ]);
    }
}