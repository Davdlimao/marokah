<?php

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assinaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('status', 20)->default(SubscriptionStatus::ATIVA->value)->index();
            $table->string('periodicidade', 20)->index();              // snapshot do plano
            $table->decimal('valor', 10, 2)->nullable();               // override (null = usar valor do plano)
            $table->timestamp('trial_ends_at')->nullable();
            $table->date('started_at')->nullable();
            $table->date('next_billing_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->text('obs')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assinaturas');
    }
};
