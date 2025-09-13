<?php

namespace App\Filament\Clusters\Configuracoes;

use Filament\Clusters\Cluster;

class ConfiguracoesCluster extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Configurações';
    protected static ?string $slug            = 'configuracoes';
    protected static string|\UnitEnum|null $navigationGroup = 'Administração';
    protected static ?int    $navigationSort  = 90;
}
