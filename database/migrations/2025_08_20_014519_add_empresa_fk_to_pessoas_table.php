<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pessoas', function (Blueprint $table) {
            // Garanta o tipo compatível com empresas.id (unsignedBigInteger)
            // Se já for unsignedBigInteger, o change() não altera nada.
            $table->unsignedBigInteger('empresa_id')->change();

            // Índice + FK (nome explícito para ficar fácil de dropar depois)
            $table->foreign('empresa_id', 'pessoas_empresa_id_fk')
                ->references('id')->on('empresas')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // ou ->restrict() se preferir bloquear exclusões
        });
    }

    public function down(): void
    {
        Schema::table('pessoas', function (Blueprint $table) {
            $table->dropForeign('pessoas_empresa_id_fk');
            // opcional: $table->index('empresa_id');  // se quiser manter só o índice
        });
    }
};
