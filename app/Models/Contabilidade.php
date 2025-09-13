<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contabilidade extends Model
{
    use SoftDeletes;
    
    protected $table = 'contabilidades';

    protected $fillable = [
        'empresa_id',
        'razao_social',
        'cnpj',
        'nome_contato',
        'email',
        'telefone',
        'principal',
        'observacoes',
        'user_id',
    ];

    protected $casts = [
        'principal' => 'bool',
    ];

    // --- setters: guardam apenas dígitos
    protected function cnpj(): Attribute
    {
        return Attribute::make(
            set: fn ($v) => $v ? preg_replace('/\D+/', '', (string) $v) : null
        );
    }

    protected function telefone(): Attribute
    {
        return Attribute::make(
            set: fn ($v) => $v ? preg_replace('/\D+/', '', (string) $v) : null
        );
    }

    // --- getters “formatados” para exibição
    public function getCnpjFormatadoAttribute(): ?string
    {
        $v = preg_replace('/\D+/', '', (string) $this->cnpj);
        if (strlen($v) !== 14) return null;
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $v);
    }

    public function getTelefoneFormatadoAttribute(): ?string
    {
        $v = preg_replace('/\D+/', '', (string) $this->telefone);
        if ($v === '') return null;

        return match (strlen($v)) {
            11 => preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $v), // celular
            10 => preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $v), // fixo
            default => null,
        };
    }
}
