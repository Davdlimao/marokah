<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Endereco extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'empresa_id','tipo','rotulo','cep','rua','numero','complemento',
        'referencia','bairro','cidade','uf','padrao',
    ];

    protected $casts = ['padrao' => 'bool'];

    protected static function booted(): void
    {
        static::saving(function (self $e) {
            $e->cep = $e->cep ? preg_replace('/\D+/', '', $e->cep) : null;
            $e->uf  = $e->uf ? strtoupper($e->uf) : null;
        });

        static::saved(function (self $e) {
            if ($e->padrao) {
                static::where('empresa_id', $e->empresa_id)
                    ->where('id', '!=', $e->id)
                    ->update(['padrao' => false]);
            }
        });
    }

    public function cliente()
    {
        return $this->belongsTo(\App\Models\Cliente::class, 'empresa_id');
    }
}
