<?php

namespace App\Policies;

use App\Models\Papel;
use App\Models\User;

class PapelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function view(User $user, Papel $papel): bool
    {
        return $user->hasRole('superadmin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function update(User $user, Papel $papel): bool
    {
        if ($papel->bloqueado || $papel->name === 'superadmin') {
            return false;
        }
        return $user->hasRole('superadmin');
    }

    public function delete(User $user, Papel $papel): bool
    {
        if ($papel->bloqueado || $papel->name === 'superadmin') {
            return false;
        }
        return $user->hasRole('superadmin');
    }
}
