<?php

namespace App\Policies;

use App\Models\Plano;
use App\Models\User;

class PlanoPolicy
{
    public function before(User $user): ?bool
    {
        // Superadmin vê tudo
        if ($user->hasRole('superadmin')) return true;
        return null;
    }

    private function canManage(User $user): bool
    {
        return method_exists($user, 'hasRole') &&
            ($user->hasRole('marokah_admin') || $user->hasRole('marokah_staff'));
    }

    public function viewAny(User $user): bool         { return $this->canManage($user); }
    public function view(User $user, Plano $p): bool  { return $this->canManage($user); }
    public function create(User $user): bool          { return $this->canManage($user); }
    public function update(User $user, Plano $p): bool{ return $this->canManage($user); }
    public function delete(User $user, Plano $p): bool{ return $this->canManage($user); }

    public function restore(User $user, Plano $p): bool     { return $this->canManage($user); }
    public function forceDelete(User $user, Plano $p): bool { return $this->canManage($user); }
}
