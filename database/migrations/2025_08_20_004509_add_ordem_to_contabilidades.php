<?php

// database/migrations/XXXX_XX_XX_XXXXXX_add_ordem_to_contabilidades_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('contabilidades', function (Blueprint $table) {
            if (! Schema::hasColumn('contabilidades', 'ordem')) {
                $table->unsignedInteger('ordem')->default(0)->after('principal');
            }
        });
    }
    public function down(): void {
        Schema::table('contabilidades', function (Blueprint $table) {
            if (Schema::hasColumn('contabilidades', 'ordem')) {
                $table->dropColumn('ordem');
            }
        });
    }
};
