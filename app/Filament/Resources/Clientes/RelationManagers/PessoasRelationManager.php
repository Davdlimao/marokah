<?php

namespace App\Filament\Resources\Clientes\RelationManagers;

use App\Support\BrDocuments;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;

class PessoasRelationManager extends RelationManager
{
    protected static string $relationship = 'pessoas';
    protected static ?string $title = 'Pessoas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo')
                    ->label('Tipo de contato')
                    ->options([
                        'representante' => 'Representante',
                        'financeiro'    => 'Financeiro',
                        'compras'       => 'Compras',
                        'fiscal'        => 'Fiscal',
                        'comercial'     => 'Comercial',
                        'suporte'       => 'Suporte',
                        'ti'            => 'TI',
                        'outro'         => 'Outro',
                    ])
                    ->required()
                    ->native(false)
                    ->columnSpan(5),

                TextInput::make('nome')
                    ->label('Nome completo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(5),

                TextInput::make('cargo')
                    ->label('Cargo')
                    ->maxLength(255)
                    ->columnSpan(3),

                // --- flags de feedback ---
                Hidden::make('cpf_ok')->dehydrated(false)->reactive(),
                Hidden::make('cpf_hint')->dehydrated(false)->reactive(),
                Hidden::make('email_ok')->dehydrated(false)->reactive(),
                Hidden::make('email_hint')->dehydrated(false)->reactive(),
                Hidden::make('tel_ok')->dehydrated(false)->reactive(),
                Hidden::make('tel_hint')->dehydrated(false)->reactive(),

                // CPF
                TextInput::make('cpf')
                    ->label('CPF')
                    ->mask('999.999.999-99')
                    ->stripCharacters(['.', '-'])              // salva apenas dígitos
                    ->live(onBlur: true)
                    ->rules([
                        function ($attribute, $value, $fail) {
                            $d = preg_replace('/\D+/', '', (string) $value);
                            if ($d && !BrDocuments::cpf($d)) {
                                $fail('CPF inválido.');
                            }
                        },
                    ])
                    ->suffixIcon(fn ($get) => match ($get('cpf_ok')) {
                        true  => 'heroicon-m-check-circle',
                        false => 'heroicon-m-x-circle',
                        default => null,
                    })
                    ->suffixIconColor(fn ($get) => $get('cpf_ok') ? 'success' : 'danger')
                    ->hint(fn ($get) => $get('cpf_hint'))
                    ->hintColor(fn ($get) => $get('cpf_ok') ? 'success' : 'danger')
                    ->afterStateUpdated(function ($set, $get, ?string $state) {
                        $d = preg_replace('/\D+/', '', (string) $state);
                        if (!$d) {
                            $set('cpf_ok', null);
                            $set('cpf_hint', null);
                            return;
                        }
                        $ok = BrDocuments::cpf($d);
                        $set('cpf_ok', $ok);
                        $set('cpf_hint', $ok ? 'CPF válido.' : 'CPF inválido.');
                    })
                    ->columnSpan(3),

                // E-mail
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->suffixIcon(fn ($get) => match ($get('email_ok')) {
                        true  => 'heroicon-m-check-circle',
                        false => 'heroicon-m-x-circle',
                        default => null,
                    })
                    ->suffixIconColor(fn ($get) => $get('email_ok') ? 'success' : 'danger')
                    ->hint(fn ($get) => $get('email_hint'))
                    ->hintColor(fn ($get) => $get('email_ok') ? 'success' : 'danger')
                    ->afterStateUpdated(function ($set, $get, ?string $state) {
                        if ($state === null || $state === '') {
                            $set('email_ok', null);
                            $set('email_hint', null);
                            return;
                        }
                        $ok = filter_var($state, FILTER_VALIDATE_EMAIL) !== false;
                        $set('email_ok', $ok);
                        $set('email_hint', $ok ? 'E-mail válido.' : 'E-mail inválido.');
                    })
                    ->columnSpan(4),

                // Telefone fixo
                TextInput::make('telefone')
                    ->label('Telefone')
                    ->mask('(99) 9999-9999')                   // máscara amigável
                    ->stripCharacters(['(', ')', ' ', '-'])    // salva só dígitos
                    ->live(onBlur: true)
                    ->rules(['nullable', 'regex:/^\d{10}$/'])        // 10 dígitos (fixo)
                    ->validationMessages([
                        'regex' => 'Telefone inválido.',
                    ])
                    ->suffixIcon(fn ($get) => match ($get('tel_ok')) {
                        true  => 'heroicon-m-check-circle',
                        false => 'heroicon-m-x-circle',
                        default => null,
                    })
                    ->suffixIconColor(fn ($get) => $get('tel_ok') ? 'success' : 'danger')
                    ->hint(fn ($get) => $get('tel_hint'))
                    ->hintColor(fn ($get) => $get('tel_ok') ? 'success' : 'danger')
                    ->afterStateUpdated(function ($set, $get, ?string $state) {
                        if (!$state) { $set('tel_ok', null); $set('tel_hint', null); return; }
                        $d = preg_replace('/\D+/', '', (string) $state);
                        $ok = (bool) preg_match('/^\d{10}$/', $d);
                        $set('tel_ok', $ok);
                        $set('tel_hint', $ok ? 'Telefone válido.' : 'Telefone inválido.');
                    })
                    ->columnSpan(3),

                // Celular / WhatsApp com validação visual
                Hidden::make('celular_ok')->dehydrated(false)->reactive(),
                Hidden::make('celular_hint')->dehydrated(false)->reactive(),
                TextInput::make('celular')
                    ->label('Celular / WhatsApp')
                    ->mask('(99) 99999-9999')
                    ->stripCharacters(['(', ')', ' ', '-'])
                    ->live(onBlur: true)
                    ->rules(['nullable', 'regex:/^\d{11}$/'])        // 11 dígitos (celular)
                    ->validationMessages([
                        'regex' => 'Celular inválido.',
                    ])
                    ->suffixIcon(fn ($get) => match ($get('celular_ok')) {
                        true  => 'heroicon-m-check-circle',
                        false => 'heroicon-m-x-circle',
                        default => null,
                    })
                    ->suffixIconColor(fn ($get) => $get('celular_ok') ? 'success' : 'danger')
                    ->hint(fn ($get) => $get('celular_hint'))
                    ->hintColor(fn ($get) => $get('celular_ok') ? 'success' : 'danger')
                    ->afterStateUpdated(function ($set, $get, ?string $state) {
                        if (!$state) { $set('celular_ok', null); $set('celular_hint', null); return; }
                        $d = preg_replace('/\D+/', '', (string) $state);
                        $ok = (bool) preg_match('/^\d{11}$/', $d);
                        $set('celular_ok', $ok);
                        $set('celular_hint', $ok ? 'Celular válido.' : 'Celular inválido.');
                    })
                    ->columnSpan(3),

                Forms\Components\Toggle::make('principal')
                    ->label('Contato principal?')
                    ->inline(false)
                    ->columnSpan(2),

                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(4)
                    ->columnSpan(9),
            ])
            ->columns(12);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')->label('Nome')->searchable()->sortable(),
                TextColumn::make('tipo')->label('Tipo')->badge()->sortable(),
                TextColumn::make('cargo')->label('Cargo')->toggleable(),
                TextColumn::make('email')->label('E-mail')->copyable()->toggleable(),
                TextColumn::make('celular')->label('Celular')->toggleable(),

                TextColumn::make('principal_label')
                    ->label('Principal')
                    ->state(fn (\App\Models\Pessoa $record) => $record->principal ? 'Principal' : 'Não')
                    ->badge() // sem argumento
                    ->color(fn (\App\Models\Pessoa $record) => $record->principal ? 'success' : 'warning'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nova pessoa')
                    ->modalHeading('Criar Pessoa'),
            ])
            ->recordActions([
                EditAction::make()->modalHeading('Editar Pessoa'),

                // <-- AÇÃO "DEFINIR COMO PRINCIPAL"
                Action::make('tornarPrincipal')
                    ->label('Definir como principal')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (\App\Models\Pessoa $record) => ! $record->principal)
                    ->requiresConfirmation()
                    ->action(function (\App\Models\Pessoa $record) {
                        // Desmarca todas as outras pessoas do cliente
                        \App\Models\Pessoa::where('empresa_id', $record->empresa_id)
                            ->update(['principal' => false]);

                        // Marca esta como principal
                        $record->update(['principal' => true]);
                    })
                    ->successNotificationTitle('Contato marcado como principal'),

        DeleteAction::make()->label('Excluir'),
    ])
            ->defaultSort('principal', 'desc')
            
            ->groupedBulkActions([
                DeleteBulkAction::make()->label('Excluir selecionados'),
            ]);
    
    }
}
