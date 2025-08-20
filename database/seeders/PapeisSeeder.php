<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PapeisSeeder extends Seeder
{
    public function run(): void
    {
        // Renomeia papéis antigos "contador_*" se existirem
        foreach (['contador_admin' => 'contabilidade_admin', 'contador_operador' => 'contabilidade_operador'] as $old => $new) {
            $r = Role::where('name', $old)->first();
            if ($r && ! Role::where('name', $new)->exists()) {
                $r->name = $new;
                $r->save();
            }
        }

        // Cria (ou garante) todos os papéis
        foreach ([
            'superadmin',
            'loja_admin','loja_operador',
            'produtor_admin','produtor_operador',
            'contabilidade_admin','contabilidade_operador',
        ] as $papel) {
            Role::firstOrCreate(['name' => $papel]);
        }
    }
}
