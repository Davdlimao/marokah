<?php

namespace App\Filament\Resources\Clientes;

use App\Filament\Resources\Clientes\Pages;
use App\Filament\Resources\Clientes\Schemas\ClienteForm;
use App\Filament\Resources\Clientes\Tables\ClientesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClienteResource extends Resource
{
    /**
     * Modelo Eloquent associado ao recurso.
     */
    protected static ?string $model = \App\Models\Cliente::class;

    /**
     * Ícone de navegação no painel.
     *
     * @var string|\BackedEnum|null
     */
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    /**
     * Rótulo singular do modelo.
     */
    protected static ?string $modelLabel = 'Cliente';

    /**
     * Rótulo plural do modelo.
     */
    protected static ?string $pluralModelLabel = 'Clientes';

    /**
     * Rótulo de navegação no menu.
     */
    protected static ?string $navigationLabel = 'Clientes';

    /**
     * Atributo usado como título do registro.
     */
    protected static ?string $recordTitleAttribute = 'razao_social';

    /**
     * Retorna o grupo de navegação do recurso.
     *
     * @return \UnitEnum|string|null
     */
    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        return 'Gestão de clientes';
    }

    /**
     * Define o schema do formulário do recurso.
     *
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return ClienteForm::make($schema);
    }

    /**
     * Define a tabela de listagem do recurso.
     *
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return ClientesTable::make($table);
    }

    /**
     * Retorna os relation managers associados ao recurso.
     *
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Clientes\RelationManagers\EnderecosRelationManager::class,
            \App\Filament\Resources\Clientes\RelationManagers\PessoasRelationManager::class,
            \App\Filament\Resources\Clientes\RelationManagers\ContabilidadeRelationManager::class,
        ];
    }

    /**
     * Define as rotas das páginas do recurso.
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/criar'),
            'edit'   => Pages\EditCliente::route('/{record}/editar'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'razao_social',
            'nome_fantasia',
            'cpf_cnpj',
            'email_comercial',
            'telefone_comercial',
            'celular_comercial',
            'contrato',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->razao_social ?: ($record->nome_fantasia ?: 'Cliente');
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return array_filter([
            'CPF/CNPJ' => $record->cpf_cnpj ?? null,
            'Contrato' => $record->contrato ?? null,
            'Status'   => $record->status ?? null,
        ]);
    }

    public static function getGlobalSearchResultUrl(Model $record, ?string $panel = null): string
    {
        return static::getUrl('edit', ['record' => $record]);
    }

}
