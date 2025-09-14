<?php

namespace App\Models;

use Spatie\Permission\Models\Role;

class Papel extends Role
{
    protected $table = 'roles';

    protected $fillable = [
        'name', 'guard_name',
        'escopo', 'bloqueado', 'descricao',
    ];

    protected $casts = [
        'bloqueado' => 'boolean',
    ];
}
