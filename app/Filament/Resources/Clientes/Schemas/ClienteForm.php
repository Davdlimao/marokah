<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms;
use App\Support\BrDocuments;
use App\Services\CnpjLookup;

class ClienteForm
{
    /**
     * Cria e retorna o schema do formulário de cliente.
     *
     * @param Schema $schema
     * @return Schema
     */
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do cliente')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Forms\Components\Radio::make('tipo_pessoa')
                            ->label('Tipo')
                            ->options(['PF' => 'Pessoa Física', 'PJ' => 'Pessoa Jurídica'])
                            ->inline()
                            ->default('PJ')
                            ->live()
                            ->columnSpan(3),

                        Forms\Components\Hidden::make('doc_valid')->dehydrated(false)->reactive(),
                        Forms\Components\Hidden::make('doc_hint')->dehydrated(false)->reactive(),

                        Forms\Components\TextInput::make('cpf_cnpj')
                            ->label('CPF / CNPJ')
                            ->required()
                            // Remove caracteres não numéricos antes de salvar
                            ->dehydrateStateUsing(fn ($state) => preg_replace('/\D+/', '', (string) $state))
                            ->unique('empresas', 'cpf_cnpj', ignoreRecord: true)
                            ->live(onBlur: true)
                            // Máscara dinâmica conforme tipo de pessoa
                            ->mask(fn ($get) => $get('tipo_pessoa') === 'PF'
                                ? '999.999.999-99'
                                : '99.999.999/9999-99'
                            )
                            // Validação customizada de CPF/CNPJ
                            ->rules(fn ($get) => [
                                function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $doc = \App\Support\BrDocuments::onlyDigits($value);
                                    if ($get('tipo_pessoa') === 'PF') {
                                        if (! \App\Support\BrDocuments::cpf($doc)) $fail('CPF inválido.');
                                    } else {
                                        if (! \App\Support\BrDocuments::cnpj($doc)) $fail('CNPJ inválido.');
                                    }
                                },
                            ])
                            // Ícone de feedback de validação
                            ->suffixIcon(fn ($get) => match ($get('doc_valid')) {
                                true  => 'heroicon-m-check-circle',
                                false => 'heroicon-m-x-circle',
                                default => null,
                            })
                            ->suffixIconColor(fn ($get) => $get('doc_valid') ? 'success' : 'danger')
                            ->hint(fn ($get) => $get('doc_hint'))
                            ->hintColor(fn ($get) => $get('doc_valid') ? 'success' : 'danger')
                            // Após atualização do campo, valida e tenta preencher dados via CNPJ
                            ->afterStateUpdated(function ($get, $set, ?string $state) {
                                $doc = BrDocuments::onlyDigits($state);

                                if ($get('tipo_pessoa') === 'PF') {
                                    $isValid = BrDocuments::cpf($doc);
                                    $set('doc_valid', $isValid);
                                    $set('doc_hint', $isValid ? 'CPF válido.' : 'CPF inválido.');
                                    return;
                                }

                                $isValid = BrDocuments::cnpj($doc);
                                $set('doc_valid', $isValid);
                                $set('doc_hint', $isValid ? 'CNPJ válido.' : 'CNPJ inválido.');

                                if (! $isValid) return;

                                static $lastDoc = null;
                                if ($lastDoc === $doc) return;
                                $lastDoc = $doc;

                                /** @var \App\Services\CnpjLookup $lookup */
                                $lookup = app(CnpjLookup::class);
                                if ($info = $lookup->fetch($doc)) {
                                    if (! $get('razao_social') && ! empty($info['razao_social'])) {
                                        $set('razao_social', $info['razao_social']);
                                    }
                                    if (! $get('nome_fantasia') && ! empty($info['nome_fantasia'])) {
                                        $set('nome_fantasia', $info['nome_fantasia']);
                                    }
                                    if (! $get('end_rua') && ! empty($info['logradouro'])) $set('end_rua', $info['logradouro']);
                                    if (! $get('end_bairro') && ! empty($info['bairro']))   $set('end_bairro', $info['bairro']);
                                    if (! $get('end_cidade') && ! empty($info['municipio']))$set('end_cidade', $info['municipio']);
                                    if (! $get('end_uf') && ! empty($info['uf']))           $set('end_uf', $info['uf']);
                                    if (! $get('end_cep') && ! empty($info['cep']))         $set('end_cep', $info['cep']);
                                }
                            })
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('razao_social')
                            ->label(fn ($get) => $get('tipo_pessoa') === 'PF' ? 'Nome completo' : 'Razão social')
                            ->required()
                            ->columnSpan(6),

                        Forms\Components\TextInput::make('nome_fantasia')
                            ->label('Nome fantasia')
                            ->visible(fn ($get) => $get('tipo_pessoa') === 'PJ')
                            ->columnSpan(6),

                        Forms\Components\TextInput::make('inscricao_estadual')
                            ->label('Inscrição Estadual')
                            ->visible(fn ($get) => $get('tipo_pessoa') === 'PJ')
                            // Desabilita se marcado como isento
                            ->disabled(fn ($get) => (bool) $get('ie_isento'))
                            ->helperText(fn ($get) => $get('ie_isento') ? 'IE marcada como isenta.' : null)
                            ->columnSpan(3),

                        Forms\Components\Toggle::make('ie_isento')
                            ->label('IE isento?')
                            ->inline(false)
                            ->live()
                            // Limpa inscrição estadual ao marcar como isento
                            ->afterStateUpdated(function ($set, $state) {
                                if ($state) {
                                    $set('inscricao_estadual', null);
                                }
                            })
                            ->visible(fn ($get) => $get('tipo_pessoa') === 'PJ')
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('email_comercial')
                            ->label('E-mail comercial')
                            ->email()
                            ->columnSpan(4),

                        Forms\Components\TextInput::make('telefone_comercial')
                            ->label('Telefone comercial')
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('celular_comercial')
                            ->label('Celular - WhatsApp')
                            ->columnSpan(3),
                    ]),

                Section::make('Uso do sistema')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Forms\Components\TextInput::make('contrato')
                            ->label('Contrato (código interno)')
                            // Gera valor padrão sequencial
                            ->default(fn ($record) => $record?->contrato ?? \App\Models\Cliente::nextContract())
                            ->readOnly()
                            ->mask('999999')
                            ->helperText('Gerado automaticamente em ordem sequencial.')
                            ->columnSpan(4),

                        Forms\Components\Select::make('status')
                            ->label('Status no sistema')
                            ->options([
                                'ATIVADO'    => 'Ativado',
                                'DESATIVADO' => 'Desativado',
                                'SUSPENSO'   => 'Suspenso',
                                'BLOQUEADO'  => 'Bloqueado',
                                'CANCELADO'  => 'Cancelado',
                            ])
                            ->default('ATIVADO')
                            ->native(false)
                            ->columnSpan(4),

                        Forms\Components\Select::make('perfil')
                            ->label('Perfil do cliente')
                            ->options([
                                'loja'     => 'Loja',
                                'produtor' => 'Produtor',
                            ])
                            ->placeholder('Selecione...')
                            ->nullable()
                            ->default(null)
                            // Não grava string vazia
                            ->dehydrated(fn ($state) => filled($state))
                            ->native(false)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('dia_vencimento')
                            ->label('Dia de vencimento')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('observacoes')
                            ->label('Observações internas')
                            ->rows(4)
                            ->columnSpan(12),
                    ]),
            ])
            ->columns(12);
    }
}
