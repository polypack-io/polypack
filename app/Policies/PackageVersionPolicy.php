<?php

namespace App\Policies;

use App\Models\PackageVersion;
use App\Models\User;

class PackageVersionPolicy
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
    public function view(User $user, PackageVersion $packageVersion): bool
    {
        return $user->role->read_all_teams || $user->teams->contains($packageVersion->package->team_id);
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
    public function update(User $user, PackageVersion $packageVersion): bool
    {
        return $user->role->write_all_teams || $packageVersion->package->team->users()->where('user_id', $user->id)->where('write', true)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PackageVersion $packageVersion): bool
    {
        return $user->role->write_all_teams || $packageVersion->package->team->users()->where('user_id', $user->id)->where('write', true)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PackageVersion $packageVersion): bool
    {
        return $user->role->write_all_teams || $packageVersion->package->team->users()->where('user_id', $user->id)->where('write', true)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PackageVersion $packageVersion): bool
    {
        return $user->role->write_all_teams || $packageVersion->package->team->users()->where('user_id', $user->id)->where('write', true)->exists();
    }
}
