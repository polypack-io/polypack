<?php

namespace App\Services\PackageServices;

use App\Contracts\PackageProvider;

abstract class AbstractPackageService implements PackageProvider
{
    protected static bool $requiresRepository = false;

    public function getRequiresRepository(): bool
    {
        return static::$requiresRepository;
    }
}
