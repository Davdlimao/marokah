<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    /** Tabela física (mantemos a existente). */
    protected $table = 'empresas';

    /** Campos liberados para mass assignment. */
    protected $fillable = [
        // 👇 IMPORTANTE: incluir 'nome' porque a coluna é NOT NULL
        'nome',

        // Identificação / uso do sistema
        'contrato', 'status', 'dia_vencimento', 'observacoes', 'perfil',

        // Tipo e documentos
        'tipo_pessoa', 'cpf_cnpj', 'razao_social', 'nome_fantasia',
        'ie', 'ie_isento',

        // Contato comercial
        'email_comercial', 'telefone_comercial', 'celular_comercial', 'whatsapp_comercial',

        // Representante e financeiro
        'representante_nome', 'representante_cpf', 'representante_email', 'representante_celular',
        'financeiro_diferente', 'financeiro_nome', 'financeiro_celular', 'financeiro_email',

        // Endereço da empresa (matriz)
        'empresa_cep', 'empresa_endereco', 'empresa_numero', 'empresa_complemento',
        'empresa_referencia', 'empresa_bairro', 'empresa_cidade', 'empresa_uf',

        // Endereço de cobrança
        'cobranca_cep', 'cobranca_endereco', 'cobranca_numero', 'cobranca_complemento',
        'cobranca_referencia', 'cobranca_bairro', 'cobranca_cidade', 'cobranca_uf',
    ];

    protected $casts = [
        'ie_isento'            => 'bool',
        'financeiro_diferente' => 'bool',
        'dia_vencimento'       => 'integer',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    /* -------------------------- Normalizações -------------------------- */

    /** Guarda o CPF/CNPJ só com dígitos. */
    protected function cpfCnpj(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? preg_replace('/\D+/', '', $value) : null,
        );
    }

    /** Guarda o CPF do representante só com dígitos. */
    protected function representanteCpf(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? preg_replace('/\D+/', '', $value) : null,
        );
    }

    public static function nextContract(): string
    {
        $nextId = (int) (self::max('id') ?? 0) + 1;
        return str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
    }
    /* ------------------------------ Hooks ------------------------------ */

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            $m->cpf_cnpj = $m->cpf_cnpj ? preg_replace('/\D+/', '', $m->cpf_cnpj) : null;

            if (blank($m->status)) {
                $m->status = 'ATIVADO';
            }

            // garante 'nome' para NOT NULL
            if (blank($m->nome)) {
                $m->nome = $m->razao_social ?: $m->nome_fantasia;
            }
        });

        static::creating(function (self $m) {
            // gera contrato se não vier do form
            if (blank($m->contrato)) {
                $m->contrato = self::nextContract(); // 000001, 000002, ...
            }
        });
    }

    /* --------------------------- Acessores ----------------------------- */

    /** CPF/CNPJ formatado para exibição. */
    public function getCpfCnpjFormatadoAttribute(): ?string
    {
        $v = $this->cpf_cnpj;
        if (!$v) return null;

        if (strlen($v) === 11) {
            return vsprintf('%s%s%s.%s%s%s.%s%s%s-%s%s', str_split($v));
        }

        if (strlen($v) === 14) {
            return substr($v,0,2).'.'.substr($v,2,3).'.'.substr($v,5,3).'/'.substr($v,8,4).'-'.substr($v,12,2);
        }

        return $v;
    }

    /* ----------------------------- Scopes ------------------------------ */

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ATIVADO');
    }

    public function scopeBusca($query, ?string $termo)
    {
        if (!$termo) return $query;

        $digitos = preg_replace('/\D+/', '', $termo);

        return $query->where(function ($q) use ($termo, $digitos) {
            $q->where('razao_social', 'like', "%{$termo}%")
              ->orWhere('nome_fantasia', 'like', "%{$termo}%")
              ->orWhere('contrato', 'like', "%{$termo}%")
              ->orWhere('cpf_cnpj', 'like', "%{$digitos}%");
        });
    }

    /* --------------------------- Relacionamentos --------------------------- */

    public function enderecos(): HasMany
    {
        // FK usada nas migrations/Model de endereço
        return $this->hasMany(\App\Models\Endereco::class, 'empresa_id');
    }

    public function pessoas(): HasMany
    {
        return $this->hasMany(\App\Models\Pessoa::class, 'empresa_id');
    }
    public function contabilidades()
    {
        return $this->hasMany(\App\Models\Contabilidade::class, 'empresa_id');
    }

}
