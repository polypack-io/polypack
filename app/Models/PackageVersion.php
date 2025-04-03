<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageVersion extends Model
{
    protected $fillable = [
        'package_id',
        'version',
        'file_id',
        'data',
        'is_dev',
        'is_private',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getNameAttribute(): string
    {
        return $this->package->name.' - Version - '.$this->version;
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function installs(): HasMany
    {
        return $this->hasMany(PackageInstall::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
