<?php

namespace App\Policies;

use App\Models\ClientAccess;
use App\Models\User;

class ClientAccessPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClientAccess $clientAccess): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role->manage_clients;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClientAccess $clientAccess): bool
    {
        return $user->role->manage_clients;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClientAccess $clientAccess): bool
    {
        return $user->role->manage_clients;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClientAccess $clientAccess): bool
    {
        return $user->role->manage_clients;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClientAccess $clientAccess): bool
    {
        return $user->role->manage_clients;
    }
}
