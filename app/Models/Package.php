<?php

namespace App\Models;

use App\Contracts\PackageProvider;
use App\Enums\Packages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'team_id',
        'repository_provider_id',
        'storage_provider_id',
        'name',
        'slug',
        'type',
        'data',
        'is_private',
        'versions_updated_at',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return BelongsTo<RepositoryProvider>
     */
    public function repositoryProvider(): BelongsTo
    {
        return $this->belongsTo(RepositoryProvider::class);
    }

    public function packageProvider(): PackageProvider
    {
        return app(Packages::from($this->type)->getService());
    }

    /**
     * @return BelongsTo<StorageProvider>
     */
    public function storageProvider(): BelongsTo
    {
        return $this->belongsTo(StorageProvider::class);
    }

    /**
     * @return HasMany<PackageVersion>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(PackageVersion::class);
    }
}
