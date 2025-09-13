<?php

namespace Database\Seeders;

use App\Models\Plano;
use Illuminate\Database\Seeder;

class PlanoSeeder extends Seeder
{
    public function run(): void
    {
        Plano::updateOrCreate(
            ['slug' => 'basico'],
            [
                'nome' => 'Básico',
                'periodicidade' => 'MENSAL',
                'valor' => 99.90,
                'status' => 'ATIVO',
                'recursos' => ['atendimento'=>'Chat e e-mail','relatorios'=>'Relatórios básicos'],
                'limites'  => ['usuarios'=>3, 'unidades'=>1],
                'ordem' => 1,
            ],
        );

        Plano::updateOrCreate(
            ['slug' => 'pro'],
            [
                'nome' => 'Pro',
                'periodicidade' => 'MENSAL',
                'valor' => 199.90,
                'status' => 'ATIVO',
                'recursos' => ['atendimento'=>'Prioritário','integracoes'=>'Integrações avançadas'],
                'limites'  => ['usuarios'=>10, 'unidades'=>3],
                'ordem' => 2,
            ],
        );
    }
}
