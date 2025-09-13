<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $fillable = [
        'fatura_id','metodo','status','valor','paid_at','gateway_ref','notes',
    ];

    protected $casts = [
        'metodo'  => PaymentMethod::class,
        'status'  => PaymentStatus::class,
        'valor'   => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function fatura() { return $this->belongsTo(Fatura::class); }
}
