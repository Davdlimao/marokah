<?php

namespace App\Filament\Resources\Papeis;

use App\Filament\Resources\Papeis\Pages\CreatePapeis;
use App\Filament\Resources\Papeis\Pages\EditPapeis;
use App\Filament\Resources\Papeis\Pages\ListPapeis;
use App\Filament\Resources\Papeis\Schemas\PapeisForm;
use App\Filament\Resources\Papeis\Tables\PapeisTable;
use App\Models\Papel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PapeisResource extends Resource
{
    protected static ?string $model = Papel::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Papéis';
    protected static ?string $modelLabel      = 'Papel';
    protected static ?string $pluralModelLabel= 'Papéis';
    protected static string|\UnitEnum|null $navigationGroup = 'Usuários';
    protected static ?int    $navigationSort  = 20;

    public static function form(Schema $schema): Schema
    {
        return PapeisForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return PapeisTable::make($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPapeis::route('/'),
            'create' => CreatePapeis::route('/criar'),
            'edit'   => EditPapeis::route('/{record}/editar'),
        ];
    }
}
