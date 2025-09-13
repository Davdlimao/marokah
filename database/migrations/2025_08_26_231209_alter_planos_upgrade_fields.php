<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Novas colunas (sem quebrar nada existente)
        Schema::table('planos', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('nome');                 // será preenchido abaixo
            $table->enum('status', ['ATIVO', 'INATIVO'])->default('ATIVO')->after('valor');
            $table->decimal('taxa_adesao', 10, 2)->nullable()->after('valor');
            $table->unsignedTinyInteger('trial_dias')->default(0)->after('taxa_adesao');
            $table->json('recursos')->nullable()->after('descricao');
            $table->json('limites')->nullable()->after('recursos');
            $table->smallInteger('ordem')->default(0)->after('limites');
            $table->boolean('oculto')->default(false)->after('ordem');
        });

        // 2) Periodicidade -> ENUM (mantendo MySQL; sem DBAL)
        DB::statement("UPDATE planos SET periodicidade = UPPER(periodicidade)");
        DB::statement("
            ALTER TABLE planos
            MODIFY periodicidade ENUM('MENSAL','TRIMESTRAL','SEMESTRAL','ANUAL')
            NOT NULL DEFAULT 'MENSAL'
        ");

        // 3) Migrar 'ativo' (bool) -> 'status' (enum)
        if (Schema::hasColumn('planos', 'ativo')) {
            DB::statement("UPDATE planos SET `status` = CASE WHEN ativo = 1 THEN 'ATIVO' ELSE 'INATIVO' END");
            // remover índice antigo e coluna
            try {
                Schema::table('planos', function (Blueprint $table) {
                    // índice criado na migration original: index(['ativo','periodicidade'])
                    $table->dropIndex('planos_ativo_periodicidade_index');
                });
            } catch (\Throwable $e) { /* índice pode não existir em alguns ambientes */ }

            Schema::table('planos', function (Blueprint $table) {
                $table->dropColumn('ativo');
            });
        }

        // 4) Preencher slugs únicos de forma segura
        $usados = DB::table('planos')->pluck('slug')->filter()->all();
        $usados = array_fill_keys($usados, true);

        $rows = DB::table('planos')->select('id','nome','periodicidade')->orderBy('id')->get();

        foreach ($rows as $row) {
            $base = Str::slug($row->nome);
            // se houver planos com o mesmo nome em periodicidades diferentes,
            // o sufixo com a primeira letra evita colisão
            $candidate = $base;
            if (isset($usados[$candidate])) {
                $candidate = $base . '-' . strtolower(substr((string)$row->periodicidade, 0, 1));
            }
            $slug = $candidate;
            $n = 1;
            while (isset($usados[$slug]) || DB::table('planos')->where('slug', $slug)->exists()) {
                $slug = $candidate . '-' . $n++;
            }
            $usados[$slug] = true;

            DB::table('planos')->where('id', $row->id)->update(['slug' => $slug]);
        }

        // 5) Índices/constraints novas
        Schema::table('planos', function (Blueprint $table) {
            // mantém o unique(['nome','periodicidade']) existente
            $table->unique('slug');
            $table->index(['status', 'periodicidade']);   // substitui o índice antigo
            $table->index('ordem');
        });
    }

    public function down(): void
    {
        // voltar periodicidade para VARCHAR(20) e status -> ativo
        DB::statement("ALTER TABLE planos MODIFY periodicidade VARCHAR(20) NOT NULL");

        Schema::table('planos', function (Blueprint $table) {
            $table->boolean('ativo')->default(true);
        });

        DB::statement("UPDATE planos SET ativo = (status = 'ATIVO')");

        // remover índices/colunas novas
        Schema::table('planos', function (Blueprint $table) {
            // dropar índices (nomes gerados automaticamente pelo Laravel)
            try { $table->dropUnique('planos_slug_unique'); } catch (\Throwable $e) {}
            try { $table->dropIndex('planos_status_periodicidade_index'); } catch (\Throwable $e) {}
            try { $table->dropIndex('planos_ordem_index'); } catch (\Throwable $e) {}

            $table->dropColumn([
                'slug', 'status', 'taxa_adesao', 'trial_dias',
                'recursos', 'limites', 'ordem', 'oculto',
            ]);
        });

        // recria índice antigo (se desejar simetria total com a migration inicial)
        try {
            Schema::table('planos', function (Blueprint $table) {
                $table->index(['ativo', 'periodicidade']); // planos_ativo_periodicidade_index
            });
        } catch (\Throwable $e) {}
    }
};
