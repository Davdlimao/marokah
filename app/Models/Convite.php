<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Convite extends Model
{
    protected $table = 'convites';

    protected $fillable = [
        'email','nome','papeis','token_hash','expira_em','usado_em','convidado_por_id',
    ];

    protected $casts = [
        'papeis'    => 'array',
        'expira_em' => 'datetime',
        'usado_em'  => 'datetime',
    ];

    public function scopeValido($q)
    {
        return $q->whereNull('usado_em')
                 ->where(function ($w) {
                     $w->whereNull('expira_em')->orWhere('expira_em', '>', now());
                 });
    }
}
