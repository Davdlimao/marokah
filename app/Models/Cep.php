<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cep extends Model
{
    protected $table = 'cep'; // tabela criada pelo plugin
    protected $fillable = ['cep', 'state', 'city', 'neighborhood', 'street'];
    public $timestamps = true;
}
