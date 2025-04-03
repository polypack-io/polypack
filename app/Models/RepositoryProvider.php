<?php

namespace App\Models;

use App\Contracts\RepositoryProvider as ContractsRepositoryProvider;
use App\Enums\Repositories;
use Illuminate\Database\Eloquent\Model;

class RepositoryProvider extends Model
{
    protected $fillable = [
        'name',
        'type',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getService(): ContractsRepositoryProvider
    {
        return app(Repositories::from($this->type)->getService(), ['credentials' => $this->data]);
    }
}
