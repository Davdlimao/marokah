<?php

namespace App\Filament\Resources\Clientes\RelationManagers;

use App\Models\Endereco;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Filament\Actions\Action;

/**
 * Gerencia o relacionamento de endereços do cliente no painel Filament.
 */
class EnderecosRelationManager extends RelationManager
{
    protected static string $relationship = 'enderecos';
    protected static ?string $recordTitleAttribute = 'rua';
    protected static ?string $title = 'Endereços';

    /**
     * Define o formulário para criação/edição de endereços.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'principal' => 'Principal',
                        'cobranca'  => 'Cobrança',
                        'entrega'   => 'Entrega',
                        'outro'     => 'Outro',
                    ])
                    ->native(false)
                    ->default('principal')
                    ->columnSpan(3),

                TextInput::make('rotulo')
                    ->label('Rótulo (ex.: Matriz, Filial 2)')
                    ->maxLength(60)
                    ->columnSpan(5),

                Hidden::make('cep_ok')->dehydrated(false)->reactive(),
                Hidden::make('cep_hint')->dehydrated(false)->reactive(),

                TextInput::make('cep')
                    ->label('CEP')
                    ->mask('99999-999')
                    ->stripCharacters(['-'])
                    ->live(onBlur: true)
                    ->required()
                    ->rule('regex:/^\d{5}-?\d{3}$/')
                    ->suffixIcon(fn ($get) => match ($get('cep_ok')) {
                        true  => 'heroicon-m-check-circle',
                        false => 'heroicon-m-x-circle',
                        default => null,
                    })
                    ->suffixIconColor(fn ($get) => $get('cep_ok') ? 'success' : 'danger')
                    ->hint(fn ($get) => $get('cep_hint'))
                    ->hintColor(fn ($get) => $get('cep_ok') ? 'success' : 'danger')
                    ->suffixAction(
                        \Filament\Actions\Action::make('buscar-cep')
                            ->icon('heroicon-o-magnifying-glass')
                            ->action(function ($get, $set, $state) {
                                /**
                                 * Busca informações do CEP informado, preenchendo automaticamente os campos de endereço.
                                 */
                                if (! $state) {
                                    $set('cep_ok', null);
                                    $set('cep_hint', null);
                                    return;
                                }

                                $cep = preg_replace('/\D+/', '', $state);
                                if (strlen($cep) !== 8) {
                                    $set('cep_ok', false);
                                    $set('cep_hint', 'CEP inválido.');
                                    return;
                                }

                                $row = DB::table('cep')->where('cep', $cep)->first();

                                if (! $row) {
                                    try {
                                        $res = Http::acceptJson()->timeout(6)->get("https://brasilapi.com.br/api/cep/v2/{$cep}");
                                        if ($res->successful()) {
                                            $data = $res->json();
                                            $row = (object) [
                                                'street'       => $data['street']       ?? ($data['logradouro'] ?? null),
                                                'neighborhood' => $data['neighborhood'] ?? ($data['bairro'] ?? null),
                                                'city'         => $data['city']         ?? null,
                                                'state'        => $data['state']        ?? null,
                                            ];
                                        }
                                    } catch (\Throwable $e) {
                                        // Falha silenciosa ao buscar CEP externo
                                    }
                                }

                                if (! $row) {
                                    $set('cep_ok', false);
                                    $set('cep_hint', 'CEP não encontrado.');
                                    return;
                                }

                                $set('cep_ok', true);
                                $set('cep_hint', 'CEP encontrado.');

                                if (! $get('rua')    && ! empty($row->street))       $set('rua',    $row->street);
                                if (! $get('bairro') && ! empty($row->neighborhood)) $set('bairro', $row->neighborhood);
                                if (! $get('cidade') && ! empty($row->city))         $set('cidade', $row->city);
                                if (! $get('uf')     && ! empty($row->state))        $set('uf',     $row->state);
                            })
                    )
                    ->afterStateUpdated(function ($set, $get, ?string $state) {
                        /**
                         * Atualiza os campos de endereço ao alterar o CEP.
                         */
                        if (! $state) {
                            $set('cep_ok', null);
                            $set('cep_hint', null);
                            return;
                        }

                        $cep = preg_replace('/\D+/', '', $state);
                        if (strlen($cep) !== 8) {
                            $set('cep_ok', false);
                            $set('cep_hint', 'CEP inválido.');
                            return;
                        }

                        $row = DB::table('cep')->where('cep', $cep)->first();

                        if (! $row) {
                            try {
                                $res = Http::acceptJson()->timeout(6)->get("https://brasilapi.com.br/api/cep/v2/{$cep}");
                                if ($res->successful()) {
                                    $data = $res->json();
                                    $row = (object) [
                                        'street'       => $data['street']       ?? ($data['logradouro'] ?? null),
                                        'neighborhood' => $data['neighborhood'] ?? ($data['bairro'] ?? null),
                                        'city'         => $data['city']         ?? null,
                                        'state'        => $data['state']        ?? null,
                                    ];
                                }
                            } catch (\Throwable $e) {
                                // Falha silenciosa ao buscar CEP externo
                            }
                        }

                        if (! $row) {
                            $set('cep_ok', false);
                            $set('cep_hint', 'CEP não encontrado.');
                            return;
                        }

                        $set('cep_ok', true);
                        $set('cep_hint', 'CEP encontrado.');

                        if (! $get('rua')    && ! empty($row->street))       $set('rua',    $row->street);
                        if (! $get('bairro') && ! empty($row->neighborhood)) $set('bairro', $row->neighborhood);
                        if (! $get('cidade') && ! empty($row->city))         $set('cidade', $row->city);
                        if (! $get('uf')     && ! empty($row->state))        $set('uf',     $row->state);
                    })
                    ->columnSpan(3),

                TextInput::make('rua')
                    ->label('Rua / Logradouro')
                    ->columnSpan(9),

                TextInput::make('numero')
                    ->label('Número')
                    ->maxLength(30)
                    ->columnSpan(2),

                TextInput::make('complemento')
                    ->label('Complemento')
                    ->columnSpan(4),

                TextInput::make('referencia')
                    ->label('Referência')
                    ->columnSpan(4),

                TextInput::make('bairro')
                    ->label('Bairro')
                    ->columnSpan(4),

                TextInput::make('cidade')
                    ->label('Cidade')
                    ->columnSpan(5),

                Select::make('uf')
                    ->label('UF')
                    ->options(self::ufs())
                    ->native(false)
                    ->columnSpan(3),

                Select::make('padrao')
                    ->label('Endereço padrão?')
                    ->options([0 => 'Não', 1 => 'Sim'])
                    ->native(false)
                    ->default(0)
                    ->columnSpan(3),
            ])
            ->columns(12);
    }

    /**
     * Define a tabela de exibição dos endereços relacionados ao cliente.
     */
    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('padrao', 'desc')
            ->columns([
                TextColumn::make('rotulo')->label('Rótulo')->searchable(),
                TextColumn::make('tipo')->label('Tipo')->badge(),
                TextColumn::make('rua')->label('Rua')->limit(30)->searchable(),
                TextColumn::make('numero')->label('Nº')->grow(false),
                TextColumn::make('bairro')->label('Bairro')->searchable(),
                TextColumn::make('cidade')->label('Cidade')->searchable(),
                TextColumn::make('uf')->label('UF')->grow(false),
                TextColumn::make('cep')
                    ->label('CEP')
                    ->grow(false)
                    ->formatStateUsing(function ($state) {
                        /**
                         * Formata o CEP para exibição no padrão 99999-999.
                         */
                        $d = preg_replace('/\D+/', '', (string) $state);
                        return strlen($d) === 8 ? substr($d, 0, 5) . '-' . substr($d, 5) : (string) $state;
                    }),
                TextColumn::make('padrao')
                    ->label('Padrão')
                    ->state(fn ($record) => $record->padrao ? 'Padrão' : 'Não')
                    ->badge()
                    ->color(fn ($state) => $state === 'Padrão' ? 'success' : 'warning')
            ])
            ->headerActions([
                CreateAction::make()->label('Novo endereço'),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),

                Action::make('tornarPadrao')
                    ->label('Definir como padrão')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Endereco $record) => ! $record->padrao)
                    ->requiresConfirmation()
                    ->action(function (Endereco $record) {
                        /**
                         * Define o endereço selecionado como padrão e desmarca os demais.
                         */
                        Endereco::where('empresa_id', $record->empresa_id)->update(['padrao' => false]);
                        $record->update(['padrao' => true]);
                    })
                    ->successNotificationTitle('Endereço marcado como padrão'),

                DeleteAction::make()->label('Excluir'),
            ])
            ->defaultSort('padrao', 'desc')
            ->groupedBulkActions([
                DeleteBulkAction::make()->label('Excluir selecionados'),
            ]);
    }

    /**
     * Retorna a lista de UFs brasileiras.
     */
    private static function ufs(): array
    {
        return [
            'AC'=>'AC','AL'=>'AL','AP'=>'AP','AM'=>'AM','BA'=>'BA','CE'=>'CE','DF'=>'DF','ES'=>'ES',
            'GO'=>'GO','MA'=>'MA','MT'=>'MT','MS'=>'MS','MG'=>'MG','PA'=>'PA','PB'=>'PB','PR'=>'PR',
            'PE'=>'PE','PI'=>'PI','RJ'=>'RJ','RN'=>'RN','RS'=>'RS','RO'=>'RO','RR'=>'RR','SC'=>'SC',
            'SP'=>'SP','SE'=>'SE','TO'=>'TO',
        ];
    }
}
