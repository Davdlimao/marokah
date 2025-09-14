<?php

namespace Database\Seeders;

use App\Models\Papel;
use App\Models\Permissao;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PapeisSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissoes = [
            ['name' => 'usuarios.ver',        'grupo' => 'usuarios',  'descricao' => 'Ver usuários'],
            ['name' => 'usuarios.gerir',      'grupo' => 'usuarios',  'descricao' => 'Gerir usuários'],
            ['name' => 'convites.ver',        'grupo' => 'usuarios',  'descricao' => 'Ver convites'],
            ['name' => 'convites.gerir',      'grupo' => 'usuarios',  'descricao' => 'Gerir convites'],
            ['name' => 'papeis.ver',          'grupo' => 'seguranca', 'descricao' => 'Ver papéis'],
            ['name' => 'papeis.gerir',        'grupo' => 'seguranca', 'descricao' => 'Gerir papéis'],
            ['name' => 'configuracoes.ver',   'grupo' => 'config',    'descricao' => 'Ver configurações'],
            ['name' => 'configuracoes.gerir', 'grupo' => 'config',    'descricao' => 'Editar configurações'],
        ];

        foreach ($permissoes as $p) {
            Permissao::firstOrCreate(
                ['name' => $p['name'], 'guard_name' => 'web'],
                ['grupo' => $p['grupo'], 'descricao' => $p['descricao']]
            );
        }

        $superadmin = Papel::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'web'],
            ['escopo' => 'sistema', 'bloqueado' => true, 'descricao' => 'Controle total']
        );

        $clienteAdmin = Papel::firstOrCreate(
            ['name' => 'cliente-admin', 'guard_name' => 'web'],
            ['escopo' => 'cliente', 'bloqueado' => true, 'descricao' => 'Admin do cliente']
        );

        $operador = Papel::firstOrCreate(
            ['name' => 'operador', 'guard_name' => 'web'],
            ['escopo' => 'cliente', 'bloqueado' => false, 'descricao' => 'Operação diária']
        );

        $visualizador = Papel::firstOrCreate(
            ['name' => 'visualizador', 'guard_name' => 'web'],
            ['escopo' => 'cliente', 'bloqueado' => false, 'descricao' => 'Somente leitura']
        );

        $superadmin->syncPermissions(Permissao::all());
        $clienteAdmin->syncPermissions(['usuarios.ver', 'convites.ver', 'convites.gerir', 'configuracoes.ver']);
    }
}
