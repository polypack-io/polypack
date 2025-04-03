<?php

namespace App\Models;

use App\Contracts\APIClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Sanctum\HasApiTokens;

class Client extends Model implements APIClient
{
    use HasApiTokens;

    protected $fillable = [
        'name',
    ];

    public function hasAccessTo(Model $model): bool
    {
        if ($this->access()->where('accessable_id', $model->id)->where('accessable_type', $model::class)->exists()) {
            return true;
        }

        foreach ($this->groups as $group) {
            if ($group->access()->where('accessable_id', $model->id)->where('accessable_type', $model::class)->exists()) {
                return true;
            }
        }

        return false;
    }

    public function hasWriteAccessTo(Model $model): bool
    {
        return false;
    }

    public function access(): MorphMany
    {
        return $this->morphMany(ClientAccess::class, 'clientable');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }
}
