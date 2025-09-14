<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('convites', function (Blueprint $t) {
            $t->id();
            $t->string('email')->index();
            $t->string('nome')->nullable();
            $t->json('papeis')->nullable();        // se usar spatie/permission
            $t->string('token_hash', 64);          // sha256 do token
            $t->timestamp('expira_em')->nullable();
            $t->timestamp('usado_em')->nullable();
            $t->foreignId('convidado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['email', 'usado_em']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('convites');
    }
};
