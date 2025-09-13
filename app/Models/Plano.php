<?php

namespace App\Models;

use App\Enums\PlanPeriod;
use App\Enums\PlanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Plano extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome', 'slug', 'periodicidade', 'valor', 'taxa_adesao',
        'trial_dias', 'status', 'descricao', 'recursos', 'limites',
        'ordem', 'oculto',
    ];

    protected $casts = [
        'valor'         => 'decimal:2',
        'taxa_adesao'   => 'decimal:2',
        'trial_dias'    => 'integer',
        'oculto'        => 'boolean',
        'recursos'      => 'array',   // KeyValue
        'limites'       => 'array',   // KeyValue
        'periodicidade' => PlanPeriod::class,
        'status'        => PlanStatus::class, // 👈 agora é enum
    ];

    // slug automático a partir do nome
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn ($v, array $a) => $v ?: Str::slug((string)($a['nome'] ?? ''))
        );
    }

    protected static function booted(): void
    {
        static::creating(function (self $p) {
            if (is_null($p->ordem)) {
                $p->ordem = (int) (self::max('ordem') ?? 0) + 1;
            }
        });

        static::saving(function (self $p) {
            // Status sempre coerced e com default ATIVO
            $p->status = PlanStatus::coerce($p->status ?? PlanStatus::ATIVO);

            // Periodicidade sempre em maiúsculo (valor do enum)
            $period = $p->periodicidade;
            if ($period instanceof PlanPeriod) {
                $period = $period->value;
            }
            $p->periodicidade = strtoupper($period ?: 'MENSAL');
        });

        // (opcional) bloquear exclusão com clientes
        static::deleting(function (self $p) {
            if ($p->clientes()->exists()) {
                throw new \DomainException('Não é possível excluir planos com clientes vinculados.');
            }
        });
    }

    /** Label legível da periodicidade (para views/exports) */
    protected function periodicidadeLabel(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attrs) => match (strtoupper($attrs['periodicidade'] ?? '')) {
                'MENSAL' => 'Mensal',
                'TRIMESTRAL' => 'Trimestral',
                'SEMESTRAL' => 'Semestral',
                'ANUAL' => 'Anual',
                default => '—',
            },
        );
    }

    /** Helpers / accessors */
    public function getIsActiveAttribute(): bool
    {
        return ($this->status instanceof PlanStatus)
            ? $this->status === PlanStatus::ATIVO
            : mb_strtoupper((string) $this->status) === 'ATIVO';
    }

    public function getStatusLabelAttribute(): string
    {
        return ($this->status instanceof PlanStatus)
            ? $this->status->label()
            : (mb_strtoupper((string) $this->status) === 'ATIVO' ? 'ATIVO' : 'INATIVO');
    }

    /** Relações */
    public function clientes()
    {
        return $this->hasMany(\App\Models\Cliente::class, 'plano_id');
    }

    /** Scopes */
    public function scopeAtivos($q)
    {
        return $q->where('status', PlanStatus::ATIVO);
    }

    public function scopePublicos($q)
    {
        return $q->where('oculto', false);
    }

    public function scopeMensais($q)
    {
        return $q->where('periodicidade', 'MENSAL'); // 👈 corrigido
    }
}
