<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ConfiguracoesGerais extends Settings
{
    public string  $nome_do_site = '';
    public bool    $site_ativo   = true;
    public ?string $logotipo_do_site = null;
    public string  $moeda_padrao = 'BRL';

    public static function group(): string
    {
        return 'geral';
    }

    public static function defaults(): array
    {
        return [
            'nome_do_site'    => 'Marokah',
            'site_ativo'      => true,
            'logotipo_do_site'=> null,
            'moeda_padrao'    => 'BRL',
        ];
    }
}
