<?php

namespace App\Filament\Resources\Assinaturas;

use App\Filament\Resources\Assinaturas\Pages\CreateAssinatura;
use App\Filament\Resources\Assinaturas\Pages\EditAssinatura;
use App\Filament\Resources\Assinaturas\Pages\ListAssinaturas;
use App\Filament\Resources\Assinaturas\Schemas\AssinaturaForm;
use App\Filament\Resources\Assinaturas\Tables\AssinaturasTable;
use App\Models\Assinatura;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssinaturaResource extends Resource
{
    protected static ?string $model = Assinatura::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    // 👇 agrupa no menu Faturamento e define a ordem
    protected static string|\UnitEnum|null $navigationGroup = 'Faturamento';
    protected static ?string $navigationLabel = 'Assinaturas';
    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AssinaturaForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return AssinaturasTable::make($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssinaturas::route('/'),
            'create' => CreateAssinatura::route('/create'),
            'edit' => EditAssinatura::route('/{record}/edit'),
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
