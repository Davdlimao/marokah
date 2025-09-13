<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fatura_id')->constrained('faturas')->cascadeOnDelete();

            $table->string('metodo', 20)->default(PaymentMethod::PIX->value);
            $table->string('status', 20)->default(PaymentStatus::CONFIRMADO->value);
            $table->decimal('valor', 12, 2)->default(0);
            $table->timestamp('paid_at')->nullable();

            $table->string('gateway_ref')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
