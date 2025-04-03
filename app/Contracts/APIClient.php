<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface APIClient
{
    public function hasAccessTo(Model $model): bool;

    public function hasWriteAccessTo(Model $model): bool;
}
