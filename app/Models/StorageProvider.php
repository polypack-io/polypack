<?php

namespace App\Models;

use App\Contracts\StorageProvider as ContractsStorageProvider;
use App\Enums\Storage;
use Illuminate\Database\Eloquent\Model;

class StorageProvider extends Model
{
    protected $fillable = [
        'name',
        'type',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getService(): ContractsStorageProvider
    {
        return app(Storage::from($this->type)->getService(), ['data' => $this->data, 'storageProvider' => $this]);
    }
}
