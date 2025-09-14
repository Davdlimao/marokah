<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('escopo')->default('sistema')->after('guard_name'); // sistema|cliente
            $table->boolean('bloqueado')->default(false)->after('escopo');
            $table->text('descricao')->nullable()->after('bloqueado');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['escopo', 'bloqueado', 'descricao']);
        });
    }
};
