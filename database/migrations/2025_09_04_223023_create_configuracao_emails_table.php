<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuracoes_email', function (Blueprint $table) {
            $table->id();

            $table->boolean('ativo')->default(true);
            $table->string('driver')->default('smtp'); // por ora só smtp

            $table->string('host')->nullable();
            $table->unsignedSmallInteger('porta')->default(587);
            $table->string('criptografia')->nullable(); // 'tls', 'ssl' ou null

            $table->string('usuario')->nullable();
            $table->text('senha')->nullable(); // cast 'encrypted'

            $table->string('from_nome')->nullable();
            $table->string('from_email')->nullable();

            // Modo desenvolvimento: redireciona todos os envios
            $table->boolean('dev_modo')->default(false);
            $table->string('dev_redirecionar_para')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_email');
    }
};
