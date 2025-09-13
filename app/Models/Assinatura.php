<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Enums\PlanPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assinatura extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'empresa_id','plano_id','status','periodicidade','valor',
        'trial_ends_at','started_at','dia_vencimento','next_billing_at','canceled_at','obs',
    ];

    protected $casts = [
        'status'          => SubscriptionStatus::class,
        'valor'           => 'decimal:2',
        'trial_ends_at'   => 'datetime',
        'started_at'      => 'date',
        'next_billing_at' => 'date',
        'canceled_at'     => 'datetime',
        'dia_vencimento'  => 'integer',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function plano()   { return $this->belongsTo(Plano::class);   }
    public function faturas() { return $this->hasMany(Fatura::class);    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === SubscriptionStatus::ATIVA;
    }

    public function valorEfetivo(): float
    {
        return (float) ($this->valor ?? $this->plano?->valor ?? 0);
    }

    public function empresa()
    {
        // se sua tabela de clientes é "clientes", mantendo o Model Cliente
        return $this->belongsTo(\App\Models\Cliente::class, 'empresa_id');
    }

    public function nextDueDate(?\Carbon\Carbon $ref = null): ?\Carbon\Carbon
    {
        $ref = ($ref ?? now())->startOfDay();

        // dia definido na assinatura ou dia do started_at (fallback)
        $start = $this->started_at ? \Carbon\Carbon::parse($this->started_at) : null;
        $day   = (int)($this->dia_vencimento ?: ($start? $start->day : 1));

        // calcula o próximo vencimento baseado no dia do mês
        $d = $ref->copy()->day(min($day, $ref->daysInMonth));
        if ($d->lt($ref)) {
            $refNext = $ref->copy()->addMonth();
            $d = $refNext->copy()->day(min($day, $refNext->daysInMonth));
        }
        return $d;
    }

    public function getProximoVencimentoAttribute(): ?string
    {
        return optional($this->nextDueDate())->toDateString();
    }

    protected static function booted(): void
    {
        static::saving(function (self $a) {
            if ($a->isDirty(['started_at','dia_vencimento']) || blank($a->next_billing_at)) {
                $a->next_billing_at = optional($a->nextDueDate())->toDateString();
            }
        });
    }
}
