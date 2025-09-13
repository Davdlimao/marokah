<?php

use App\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('faturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('assinatura_id')->nullable()->constrained('assinaturas')->nullOnDelete();

            $table->string('status', 20)->default(InvoiceStatus::ABERTA->value)->index();
            $table->date('referencia_ini')->nullable();
            $table->date('referencia_fim')->nullable();
            $table->date('emissao')->default(now());
            $table->date('vencimento')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descontos', 12, 2)->default(0);
            $table->decimal('acrescimos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['empresa_id', 'status', 'vencimento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faturas');
    }
};
