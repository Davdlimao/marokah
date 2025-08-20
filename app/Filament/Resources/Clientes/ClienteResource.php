<?php

namespace App\Filament\Resources\Clientes;

use App\Filament\Resources\Clientes\Pages;
use App\Filament\Resources\Clientes\Schemas\ClienteForm;
use App\Filament\Resources\Clientes\Tables\ClientesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ClienteResource extends Resource
{
    protected static ?string $model = \App\Models\Cliente::class;

    /** @var string|\BackedEnum|null */
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $modelLabel       = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $navigationLabel  = 'Clientes';
    protected static ?string $recordTitleAttribute = 'razao_social';

    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        return 'Gestão de clientes';
    }

    public static function form(Schema $schema): Schema
    {
        return ClienteForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientesTable::make($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Clientes\RelationManagers\EnderecosRelationManager::class,
            \App\Filament\Resources\Clientes\RelationManagers\PessoasRelationManager::class,
            \App\Filament\Resources\Clientes\RelationManagers\ContabilidadeRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/criar'),
            'edit'   => Pages\EditCliente::route('/{record}/editar'),
        ];
    }
}
