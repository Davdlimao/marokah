<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas'; // ajuste se o nome da tabela for diferente

    protected $fillable = [
        'nome',
        'cnpj',
        'endereco',
        'telefone',
        // adicione outros campos conforme necessário
    ];

    // Adicione relacionamentos ou casts se o resource precisar
}
