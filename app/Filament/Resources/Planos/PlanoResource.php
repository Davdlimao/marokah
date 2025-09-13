<?php

namespace App\Filament\Resources\Planos;

use App\Filament\Resources\Planos\Pages\CreatePlano;
use App\Filament\Resources\Planos\Pages\EditPlano;
use App\Filament\Resources\Planos\Pages\ListPlanos;
use App\Filament\Resources\Planos\Schemas\PlanoForm;
use App\Filament\Resources\Planos\Tables\PlanosTable;
use App\Models\Plano;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PlanoResource extends Resource
{
    protected static ?string $model = Plano::class;

    protected static string|\BackedEnum|null $navigationIcon   = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null   $navigationGroup  = 'Faturamento';
    protected static ?string $navigationLabel  = 'Planos';
    protected static ?string $modelLabel       = 'Plano';
    protected static ?string $pluralModelLabel = 'Planos';
    protected static ?int    $navigationSort   = 10;

    public static function form(Schema $schema): Schema
    {
        return PlanoForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return PlanosTable::make($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPlanos::route('/'),
            'create' => CreatePlano::route('/create'),
            'edit'   => EditPlano::route('/{record}/edit'),
        ];
    }
}
