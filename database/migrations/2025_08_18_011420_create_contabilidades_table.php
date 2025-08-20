<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contabilidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->string('razao_social')->nullable();
            $table->string('cnpj', 18)->nullable(); // guardamos formatado se quiser
            $table->string('nome_contato')->nullable();
            $table->string('email')->nullable();    // pode usar unique com (empresa_id,email) se desejar
            $table->string('telefone')->nullable();
            $table->boolean('principal')->default(false);

            // caso queira vincular a um usuário do sistema:
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index(['empresa_id','principal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contabilidades');
    }
};
