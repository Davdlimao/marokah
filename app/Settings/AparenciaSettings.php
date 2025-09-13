<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AparenciaSettings extends Settings
{
    // Logos e imagens
    public ?string $logo_header = null;
    public ?string $logo_header_dark = null;
    public ?string $logo_quadrado = null;
    public ?string $favicon = null;
    public ?string $bg_login = null;
    public ?string $bg_painel = null;

    // Cores
    public string $cor_primaria = '#16A34A';
    public string $cor_secundaria = '#0F2B1F';
    public string $cor_acento = '#22C55E';
    public string $cor_sucesso = '#16A34A';
    public string $cor_aviso = '#F59E0B';
    public string $cor_erro = '#EF4444';

    // Tipografia
    public ?string $fonte_base = 'Inter';
    public ?string $fonte_titulos = 'Poppins';
    public ?string $fonte_links = null; // <link rel="preconnect"...> do Google Fonts (opcional)

    // Layout
    public string $raio_borda = '0.75rem'; // small .25, medium .5, large .75/1
    public string $densidade_ui = 'comfortable'; // comfortable|compact
    public bool $tema_escuro_default = true;

    public static function group(): string { return 'aparencia'; }
    public static function name(): string { return 'default'; }

    public static function defaults(): array
    {
        return [
            'logo_header' => null,
            'logo_header_dark' => null,
            'logo_quadrado' => null,
            'favicon' => null,
            'bg_login' => null,
            'bg_painel' => null,

            'cor_primaria' => '#16A34A',
            'cor_secundaria' => '#0F2B1F',
            'cor_acento' => '#22C55E',
            'cor_sucesso' => '#16A34A',
            'cor_aviso' => '#F59E0B',
            'cor_erro' => '#EF4444',

            'fonte_base' => 'Inter',
            'fonte_titulos' => 'Poppins',
            'fonte_links' => null,

            'raio_borda' => '0.75rem',
            'densidade_ui' => 'comfortable',
            'tema_escuro_default' => true,
        ];
    }
}
