<?php

namespace App\Filament\Clusters\Configuracoes\Pages;

use App\Filament\Clusters\Configuracoes\ConfiguracoesCluster;
use Filament\Pages\Page;

class PreferenciasGerais extends Page
{
    protected string $view = 'filament.clusters.configuracoes.pages.preferencias-gerais';

    protected static ?string $cluster = ConfiguracoesCluster::class;
}
