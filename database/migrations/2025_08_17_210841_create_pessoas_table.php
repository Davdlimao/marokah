<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    if (\Illuminate\Support\Facades\Schema::hasTable('pessoas')) {
        // já existe: apenas sai e deixa o Laravel marcar a migration como executada
        return;
    }

    \Illuminate\Support\Facades\Schema::create('pessoas', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
        $table->string('nome');
        $table->enum('tipo', ['representante','financeiro','compras','fiscal','comercial','suporte','ti','outro'])
              ->default('outro')->index();
        $table->string('cpf', 14)->nullable();
        $table->string('cargo')->nullable();
        $table->string('email')->nullable();
        $table->string('telefone')->nullable();
        $table->string('celular')->nullable();
        $table->boolean('principal')->default(false);
        $table->text('observacoes')->nullable();
        $table->timestamps();

        $table->index(['empresa_id','tipo','principal']);
    });
}
    public function down(): void
    {
        Schema::dropIfExists('pessoas');
    }
};
