<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_superadmin;
    }

    public function view(User $user, User $model): bool
    {
        return (bool) $user->is_superadmin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_superadmin;
    }

    public function update(User $user, User $model): bool
    {
        return (bool) $user->is_superadmin;
    }

    public function delete(User $user, User $model): bool
    {
        // superadmin pode deletar, mas nunca a si mesmo
        return (bool) $user->is_superadmin && $user->id !== $model->id;
    }

    public function deleteAny(User $user): bool
    {
        return (bool) $user->is_superadmin;
    }
}
