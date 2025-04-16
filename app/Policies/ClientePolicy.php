<?php

namespace App\Policies;

use App\Models\User;

class ClientePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return $user->rol == 'admin';
    }

    public function view(User $user, User $model)
    {
        return $user->rol == 'admin';
    }

    public function create(User $user)
    {
        return $user->rol == 'admin';
    }

    public function update(User $user, User $model)
    {
        return $user->rol == 'admin';
    }

    public function delete(User $user, User $model)
    {
        return $user->rol == 'admin';
    }
}
