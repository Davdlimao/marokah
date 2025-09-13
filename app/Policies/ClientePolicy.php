<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    public function before(User $user): ?bool
    {
        // Superadmin vê tudo
        if ($user->hasRole('superadmin')) return true;
        return null;
    }

    private function canManage(User $user): bool
    {
        // Ajuste estes papéis conforme seu projeto
        return method_exists($user, 'hasRole') &&
            ($user->hasRole('marokah_admin') || $user->hasRole('marokah_staff'));
    }

    public function viewAny(User $user): bool   { return $this->canManage($user); }
    public function view(User $user, Cliente $c): bool   { return $this->canManage($user); }
    public function create(User $user): bool    { return $this->canManage($user); }
    public function update(User $user, Cliente $c): bool { return $this->canManage($user); }
    public function delete(User $user, Cliente $c): bool { return $this->canManage($user); }

    // Se sua tabela tiver soft deletes:
    public function restore(User $user, Cliente $c): bool     { return $this->canManage($user); }
    public function forceDelete(User $user, Cliente $c): bool { return $this->canManage($user); }
}
