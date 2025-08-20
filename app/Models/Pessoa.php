<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    protected $fillable = [
        'empresa_id', 'tipo', 'nome', 'cargo', 'cpf', 'email',
        'telefone', 'celular', 'observacoes', 'principal',
    ];

    protected $casts = [
        'principal' => 'bool',
    ];

    protected static function booted(): void
    {
        // Normaliza CPF, opcional
        static::saving(function (self $p) {
            $p->cpf = $p->cpf ? preg_replace('/\D+/', '', $p->cpf) : null;
        });

        // Se esta pessoa for marcada como principal, desmarca as outras
        static::saved(function (self $p) {
            if ($p->principal) {
                static::where('empresa_id', $p->empresa_id)
                    ->where('id', '!=', $p->id)
                    ->update(['principal' => false]);
            }
        });
    }

    public function cliente()
    {
        return $this->belongsTo(\App\Models\Cliente::class, 'empresa_id');
    }
}
