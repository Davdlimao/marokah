<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->string('periodicidade', 20); // mensal, trimestral, semestral, anual
            $table->decimal('valor', 10, 2)->unsigned();
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['nome', 'periodicidade']);
            $table->index(['ativo', 'periodicidade']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planos');
    }
};
