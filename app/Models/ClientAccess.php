<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ClientAccess extends Model
{
    protected $fillable = [
        'clientable_id',
        'clientable_type',
        'accessable_id',
        'accessable_type',
    ];

    public function clientable(): MorphTo
    {
        return $this->morphTo();
    }

    public function accessable(): MorphTo
    {
        return $this->morphTo();
    }
}
