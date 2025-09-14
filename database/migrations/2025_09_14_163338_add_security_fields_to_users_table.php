<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'sobrenome')) {
                $table->string('sobrenome')->nullable();
            }
            if (!Schema::hasColumn('users', 'telefone')) {
                $table->string('telefone', 30)->nullable();
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->index();
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->index();
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 64)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'sobrenome')) {
                $table->dropColumn('sobrenome');
            }
            if (Schema::hasColumn('users', 'telefone')) {
                $table->dropColumn('telefone');
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
            if (Schema::hasColumn('users', 'last_login_ip')) {
                $table->dropColumn('last_login_ip');
            }
        });
    }
};
