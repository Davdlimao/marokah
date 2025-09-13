<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaturaItem extends Model
{
    protected $fillable = [
        'fatura_id','tipo','descricao','qtd','unitario','total','metadata',
    ];

    protected $casts = [
        'qtd'      => 'integer',
        'unitario' => 'decimal:2',
        'total'    => 'decimal:2',
        'metadata' => 'array',
    ];

    public function fatura() { return $this->belongsTo(Fatura::class); }
}
