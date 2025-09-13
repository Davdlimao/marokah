<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Settings\ConfiguracoesGerais;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        app(ConfiguracoesGerais::class)->save([
            'nome_do_site'     => 'Marokah',
            'site_ativo'       => true,
            'logotipo_do_site' => null,
            'moeda_padrao'     => 'BRL',
        ]);
    }
}