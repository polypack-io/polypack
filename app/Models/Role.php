<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'level',
        'read_all_teams',
        'write_all_teams',
        'manage_users',
        'manage_clients',
        'manage_settings',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
