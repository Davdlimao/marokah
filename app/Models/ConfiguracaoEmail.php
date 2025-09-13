<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoEmail extends Model
{
    protected $table = 'configuracoes_email';

    protected $fillable = [
        'ativo', 'driver',
        'host', 'porta', 'criptografia',
        'usuario', 'senha',
        'from_nome', 'from_email',
        'dev_modo', 'dev_redirecionar_para',
    ];

    protected $casts = [
        'ativo'      => 'boolean',
        'porta'      => 'integer',
        'dev_modo'   => 'boolean',
        // protege a senha em repouso (AES-256)
        'senha'      => 'encrypted',
    ];

    /**
     * Garante um registro único (singleton).
     */
    public static function unico(): self
    {
        return static::query()->firstOrCreate([], [
            'driver' => 'smtp',
            'porta'  => 587,
            'criptografia' => 'tls',
        ]);
    }
}
