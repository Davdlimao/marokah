<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('grupo')->nullable()->after('guard_name');
            $table->string('descricao')->nullable()->after('grupo');
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['grupo', 'descricao']);
        });
    }
};
