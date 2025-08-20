<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    if (! Schema::hasTable('enderecos')) {
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();

            // (deixe como estava originalmente; hoje está com cliente_id)
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();

            $table->string('tipo', 20)->default('outro');
            $table->string('rotulo')->nullable();

            $table->string('cep', 9)->nullable();
            $table->string('rua')->nullable();
            $table->string('numero', 30)->nullable();
            $table->string('complemento')->nullable();
            $table->string('referencia')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->char('uf', 2)->nullable();

            $table->boolean('padrao')->default(false);
            $table->timestamps();

            $table->index(['cliente_id', 'tipo']);
            $table->index('cep');
        });
    }
}
    public function down(): void
    {
        Schema::dropIfExists('enderecos');
    }
};
