<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AparenciaSettings extends Settings
{
    public ?string $logo_header = null;
    public ?string $favicon     = null;
    public string  $cor_primaria = '#16A34A';
    public string  $cor_secundaria = '#0F2B1F';
    public bool    $tema_escuro_default = true;

    public static function group(): string
    {
        return 'aparencia';
    }

    public static function defaults(): array
    {
        return [
            'logo_header'        => null,
            'favicon'            => null,
            'cor_primaria'       => '#16A34A',
            'cor_secundaria'     => '#0F2B1F',
            'tema_escuro_default'=> true,
        ];
    }
}
