<?php

namespace App\Filament\Clusters\Configuracoes\Pages;

use App\Filament\Clusters\Configuracoes\ConfiguracoesCluster;
use Filament\Pages\Page;

class IntegracaoEmail extends Page
{
    protected string $view = 'filament.clusters.configuracoes.pages.integracao-email';

    protected static ?string $cluster = ConfiguracoesCluster::class;
}
