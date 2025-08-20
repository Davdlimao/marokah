<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) solta a FK antiga e índices ligados a cliente_id, se existirem
        try { DB::statement('ALTER TABLE `enderecos` DROP FOREIGN KEY `enderecos_cliente_id_foreign`'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX `enderecos_cliente_id_tipo_index` ON `enderecos`'); } catch (\Throwable $e) {}

        // 2) adiciona empresa_id (nullable), copia dados e cria FK nova
        Schema::table('enderecos', function (Blueprint $table) {
            if (! Schema::hasColumn('enderecos', 'empresa_id')) {
                $table->unsignedBigInteger('empresa_id')->nullable()->after('id');
            }
        });

        DB::statement('UPDATE `enderecos` SET `empresa_id` = `cliente_id` WHERE `empresa_id` IS NULL AND `cliente_id` IS NOT NULL');

        Schema::table('enderecos', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->index(['empresa_id', 'tipo']);
        });

        // 3) remove a coluna antiga
        try {
            Schema::table('enderecos', function (Blueprint $table) {
                if (Schema::hasColumn('enderecos', 'cliente_id')) {
                    $table->dropColumn('cliente_id');
                }
            });
        } catch (\Throwable $e) {
            // ignora se já tiver sido removida
        }
    }

    public function down(): void
    {
        // inverso: recria cliente_id e volta FK
        Schema::table('enderecos', function (Blueprint $table) {
            if (! Schema::hasColumn('enderecos', 'cliente_id')) {
                $table->unsignedBigInteger('cliente_id')->nullable()->after('id');
            }
        });

        DB::statement('UPDATE `enderecos` SET `cliente_id` = `empresa_id` WHERE `cliente_id` IS NULL AND `empresa_id` IS NOT NULL');

        try { DB::statement('ALTER TABLE `enderecos` DROP FOREIGN KEY `enderecos_empresa_id_foreign`'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX `enderecos_empresa_id_tipo_index` ON `enderecos`'); } catch (\Throwable $e) {}

        Schema::table('enderecos', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->index(['cliente_id', 'tipo']);
            $table->dropColumn('empresa_id');
        });
    }
};
