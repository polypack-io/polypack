<?php

namespace App\Contracts;

use App\Models\Package;
use App\Models\PackageVersion;
use Illuminate\Database\Eloquent\Collection;

interface PackageProvider
{
    public function getPackage(string $name): ?Package;

    public function getRequiresRepository(): bool;

    public static function packageNameRules(): array;

    /**
     * @return Collection<PackageVersion>
     */
    public function getPackageVersions(Package $package, ?bool $dev = false): Collection;

    public function createVersion(Package $package, RepositoryProvider $repositoryProvider, array $repositoryData, string $label, bool $isDev = false, bool $isPrivate = false): bool;

    public function initializePackage(Package $package): void;
}
