<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    protected $fillable = [
        'storage_provider_id',
        'mime_type',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function storageProvider(): BelongsTo
    {
        return $this->belongsTo(StorageProvider::class);
    }
}
