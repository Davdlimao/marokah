<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fatura extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'empresa_id','assinatura_id','status','referencia_ini','referencia_fim',
        'emissao','vencimento','subtotal','descontos','acrescimos','total','paid_at',
        'descricao',
    ];

    protected $casts = [
        'status'         => InvoiceStatus::class,
        'referencia_ini' => 'date',
        'referencia_fim' => 'date',
        'emissao'        => 'date',
        'vencimento'     => 'date',
        'paid_at'        => 'datetime',
        'subtotal'       => 'decimal:2',
        'descontos'      => 'decimal:2',
        'acrescimos'     => 'decimal:2',
        'total'          => 'decimal:2',
    ];

    public function cliente()     { return $this->belongsTo(Cliente::class); }
    public function assinatura()  { return $this->belongsTo(Assinatura::class); }
    public function itens()       { return $this->hasMany(FaturaItem::class); }
    public function pagamentos()  { return $this->hasMany(Pagamento::class); }

    public function recalcularTotais(): void
    {
        $subtotal = $this->itens()->sum('total');
        $this->subtotal = $subtotal;
        $this->total = $subtotal - (float)$this->descontos + (float)$this->acrescimos;
    }
    
    public function empresa()
    {
        // se sua tabela de clientes é "clientes", mantendo o Model Cliente
        return $this->belongsTo(\App\Models\Cliente::class, 'empresa_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $f) {
            $f->status   ??= \App\Enums\InvoiceStatus::ABERTA;
            $f->emissao  ??= now()->toDateString();
            // se vier assinatura e não vier vencimento, calcula pela assinatura:
            if (!$f->vencimento && $f->assinatura_id) {
                $a = \App\Models\Assinatura::find($f->assinatura_id);
                $f->vencimento = optional($a?->nextDueDate())->toDateString();
            }
        });

        static::saving(function (self $f) {
            // segurança: total coerente com os campos
            $f->total = (float)$f->subtotal - (float)$f->descontos + (float)$f->acrescimos;
        });
    }
}
