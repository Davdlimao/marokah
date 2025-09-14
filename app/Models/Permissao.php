<?php

namespace App\Models;

use Spatie\Permission\Models\Permission;

class Permissao extends Permission
{
    protected $table = 'permissions';

    protected $fillable = [
        'name', 'guard_name',
        'grupo', 'descricao',
    ];
}
