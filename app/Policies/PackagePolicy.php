<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;

class PackagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role->read_all_teams || $user->teams()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Package $package): bool
    {
        return $user->role->read_all_teams || $user->teams()->where('id', $package->team_id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role->write_all_teams || $user->teams()->where('write', true)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Package $package): bool
    {
        return $user->role->write_all_teams || $package->team->users()->where('user_id', $user->id)->where('write', true)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Package $package): bool
    {
        return $user->role->write_all_teams || $package->team->users()->where('user_id', $user->id)->where('write', true)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Package $package): bool
    {
        return $user->role->write_all_teams || $package->team->users()->where('user_id', $user->id)->where('write', true)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Package $package): bool
    {
        return $user->role->write_all_teams || $package->team->users()->where('user_id', $user->id)->where('write', true)->exists();
    }
}
