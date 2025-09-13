<?php

namespace App\Filament\Resources\Faturas;

use App\Filament\Resources\Faturas\Pages\CreateFatura;
use App\Filament\Resources\Faturas\Pages\EditFatura;
use App\Filament\Resources\Faturas\Pages\ListFaturas;
use App\Filament\Resources\Faturas\Schemas\FaturaForm;
use App\Filament\Resources\Faturas\Tables\FaturasTable;
use App\Models\Fatura;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FaturaResource extends Resource
{
    protected static ?string $model = Fatura::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    // 👇 agrupa no menu Faturamento e define a ordem
    protected static string|\UnitEnum|null $navigationGroup = 'Faturamento';
    protected static ?string $navigationLabel = 'Faturas';
    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return FaturaForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return FaturasTable::make($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaturas::route('/'),
            'create' => CreateFatura::route('/create'),
            'edit' => EditFatura::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
