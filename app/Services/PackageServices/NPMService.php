<?php

namespace App\Services\PackageServices;

use App\Contracts\RepositoryProvider;
use App\Enums\Packages;
use App\Helpers\Semver;
use App\Models\Package;
use App\Models\PackageVersion;
use Illuminate\Database\Eloquent\Collection;

class NPMService extends AbstractPackageService
{
    public function getPackage(string $name): ?Package
    {
        return Package::where('slug', $name)->where('type', Packages::NPM)->first();
    }

    public function getPackageVersions(Package $package, ?bool $dev = false): Collection
    {
        if ($dev === null) {
            return PackageVersion::query()->where('package_id', $package->id)->get();
        }

        return PackageVersion::query()->where('package_id', $package->id)->where('is_dev', $dev)->get();
    }

    public function createVersion(Package $package, RepositoryProvider $repositoryProvider, array $repositoryData, string $label, bool $isDev = false, bool $isPrivate = false): bool
    {
        if (! Semver::isValid($label)) {
            return false;
        }

        $semver = new Semver($label);

        if ($isDev === false && $semver->prerelease !== null) {
            $isDev = true;
        }

        [
            'url' => $downloadUrl,
            'type' => $type,
            'file_id' => $fileId,
        ] = $repositoryProvider->getRepositoryDownloadUrl($package, $repositoryData, 'tarball');

        $file = $repositoryProvider->getRepositoryFile($package, array_merge($repositoryData, ['file' => 'package.json']));

        $packageJson = json_decode($file, true);

        $packageJson['version'] = $label;

        $packageJson['dist'] = [
            'shasum' => '',
            'tarball' => $downloadUrl,
        ];

        $version = PackageVersion::updateOrCreate([
            'package_id' => $package->id,
            'version' => $label,
        ], [
            'file_id' => $fileId,
            'data' => $packageJson,
            'is_dev' => $isDev,
            'is_private' => $isPrivate,
        ]);

        return $version->created_at !== $version->updated_at;
    }

    public function initializePackage(Package $package): void {}

    public static function packageNameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }
}
