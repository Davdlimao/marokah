<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class IntegracaoEmailSettings extends Settings
{
    public string $texto;
    public ?int $numero;
    public bool $toggle;
    public bool $check;

    public static function group(): string
    {
        return 'integracao_email';
    }
}
