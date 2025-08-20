<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    public function before(User $user): ?bool
    {
        // Superadmin vê tudo
        if ($user->hasRole('superadmin')) return true;
        return null;
    }

    public function viewAny(User $user): bool { return false; }
    public function view(User $user, Empresa $empresa): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, Empresa $empresa): bool { return false; }
    public function delete(User $user, Empresa $empresa): bool { return false; }
}
