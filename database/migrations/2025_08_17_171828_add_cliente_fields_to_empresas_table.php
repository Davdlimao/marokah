<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Identificação / contrato / status
            $table->string('contrato')->nullable()->unique();
            $table->enum('status', ['ATIVADO','DESATIVADO','SUSPENSO','BLOQUEADO','CANCELADO'])
                ->default('ATIVADO')->index();
            $table->unsignedTinyInteger('dia_vencimento')->nullable(); // 1..31
            $table->text('observacoes')->nullable();

            // Tipo e documentos
            $table->enum('tipo_pessoa', ['PF', 'PJ'])->default('PJ');
            $table->string('cpf_cnpj', 18)->nullable()->index();
            $table->string('razao_social')->nullable();   // ou nome (PF)
            $table->string('nome_fantasia')->nullable();  // PJ
            $table->string('ie')->nullable();
            $table->boolean('ie_isento')->default(false);

            // Contato comercial
            $table->string('email_comercial')->nullable();
            $table->string('telefone_comercial')->nullable();
            $table->string('celular_comercial')->nullable();
            $table->string('whatsapp_comercial')->nullable();

            // Representante
            $table->string('representante_nome')->nullable();
            $table->string('representante_cpf', 14)->nullable();
            $table->string('representante_email')->nullable();
            $table->string('representante_celular')->nullable();
            $table->boolean('financeiro_diferente')->default(false);
            $table->string('financeiro_nome')->nullable();
            $table->string('financeiro_celular')->nullable();
            $table->string('financeiro_email')->nullable();

            // Endereço empresa (matriz)
            $table->string('empresa_cep', 9)->nullable();
            $table->string('empresa_endereco')->nullable();
            $table->string('empresa_numero', 20)->nullable();
            $table->string('empresa_complemento')->nullable();
            $table->string('empresa_referencia')->nullable();
            $table->string('empresa_bairro')->nullable();
            $table->string('empresa_cidade')->nullable();
            $table->string('empresa_uf', 2)->nullable();

            // Endereço de cobrança
            $table->string('cobranca_cep', 9)->nullable();
            $table->string('cobranca_endereco')->nullable();
            $table->string('cobranca_numero', 20)->nullable();
            $table->string('cobranca_complemento')->nullable();
            $table->string('cobranca_referencia')->nullable();
            $table->string('cobranca_bairro')->nullable();
            $table->string('cobranca_cidade')->nullable();
            $table->string('cobranca_uf', 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'contrato','status','dia_vencimento','observacoes',
                'tipo_pessoa','cpf_cnpj','razao_social','nome_fantasia','ie','ie_isento',
                'email_comercial','telefone_comercial','celular_comercial','whatsapp_comercial',
                'representante_nome','representante_cpf','representante_email','representante_celular',
                'financeiro_diferente','financeiro_nome','financeiro_celular','financeiro_email',
                'empresa_cep','empresa_endereco','empresa_numero','empresa_complemento','empresa_referencia','empresa_bairro','empresa_cidade','empresa_uf',
                'cobranca_cep','cobranca_endereco','cobranca_numero','cobranca_complemento','cobranca_referencia','cobranca_bairro','cobranca_cidade','cobranca_uf',
            ]);
        });
    }
};
