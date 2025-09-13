<?php

namespace App\Filament\Clusters\Configuracoes\Pages;

use App\Filament\Clusters\Configuracoes\ConfiguracoesCluster;
use Filament\Pages\Page;

class Aparencia extends Page
{
    protected string $view = 'filament.clusters.configuracoes.pages.aparencia';

    protected static ?string $cluster = ConfiguracoesCluster::class;
}
