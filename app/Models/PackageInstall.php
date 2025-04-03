<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageInstall extends Model
{
    protected $fillable = [
        'package_version_id',
    ];

    public function packageVersion(): BelongsTo
    {
        return $this->belongsTo(PackageVersion::class);
    }
}
