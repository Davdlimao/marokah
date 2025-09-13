<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ConfiguracoesGerais extends Settings
{
    public string $nome_do_site;
    public bool $site_ativo;
    public ?string $logotipo_do_site;
    public string $moeda_padrao;

    public static function group(): string
    {
        return 'geral';
    }
}