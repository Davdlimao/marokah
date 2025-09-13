<?php

namespace App\Filament\Resources\Usuarios;

use App\Filament\Resources\Usuarios\Pages\CreateUsuario;
use App\Filament\Resources\Usuarios\Pages\EditUsuario;
use App\Filament\Resources\Usuarios\Pages\ListUsuarios;
use App\Filament\Resources\Usuarios\Schemas\UsuarioForm;
use App\Filament\Resources\Usuarios\Tables\UsuariosTable;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class UsuariosResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup  = 'Administração';
    protected static ?string $navigationLabel  = 'Usuários';
    protected static ?string $modelLabel       = 'Usuário';
    protected static ?string $pluralModelLabel = 'Usuários';
    protected static ?int    $navigationSort   = 1;

    // **mostra o menu só para quem pode ver**
    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return \Illuminate\Support\Facades\Gate::allows('viewAny', User::class);
    }

    public static function form(Schema $schema): Schema
    {
        return UsuarioForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return UsuariosTable::make($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsuarios::route('/'),
            'create' => CreateUsuario::route('/create'),
            'edit'   => EditUsuario::route('/{record}/edit'),
        ];
    }
}
