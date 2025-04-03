<?php

namespace App\Services\PackageServices;

use App\Contracts\RepositoryProvider;
use App\Enums\Packages;
use App\Models\Package;
use App\Models\PackageVersion;
use Composer\Json\JsonFile;
use Composer\Semver\VersionParser;
use Illuminate\Database\Eloquent\Collection;

class ComposerService extends AbstractPackageService
{
    protected static bool $requiresRepository = true;

    public function getPackage(string $name): ?Package
    {
        return Package::where('slug', $name)->where('type', Packages::COMPOSER)->first();
    }

    public function getPackageVersions(Package $package, ?bool $dev = false): Collection
    {
        if ($dev === null) {
            return PackageVersion::query()->where('package_id', $package->id)->get();
        }

        return PackageVersion::query()->where('package_id', $package->id)->where('is_dev', $dev)->get();
    }

    private function prepareManifest(Package $package, array $manifest, array $data, string $version): array
    {
        $name = $manifest['name'];
        if ($name !== $package->slug) {
            $manifest['name'] = $package->slug;
        }

        $manifest['version'] = $version;
        $manifest['version_normalized'] = (new VersionParser)->normalize($version);

        return array_merge($manifest, $data);
    }

    public function createVersion(Package $package, RepositoryProvider $repositoryProvider, array $repositoryData, string $label, bool $isDev = false, bool $isPrivate = false): bool
    {
        [
            'url' => $downloadUrl,
            'type' => $type,
            'file_id' => $fileId,
        ] = $repositoryProvider->getRepositoryDownloadUrl($package, $repositoryData);

        $file = $repositoryProvider->getRepositoryFile($package, array_merge($repositoryData, ['file' => 'composer.json']));
        $composerValidate = json_decode($file, false);

        try {
            JsonFile::validateJsonSchema(
                json_encode($composerValidate),
                $composerValidate,
                JsonFile::LAX_SCHEMA
            );
        } catch (\Exception $e) {
            dd($composerValidate, $e);
        }

        $composer = json_decode($file, true);

        $composer = $this->prepareManifest(
            $package,
            $composer,
            [
                'dist' => [
                    'url' => $downloadUrl,
                    'type' => $type,
                    'shasum' => '',
                    'reference' => $repositoryData['ref'],
                ],
            ],
            $label
        );

        $version = PackageVersion::updateOrCreate([
            'package_id' => $package->id,
            'version' => $label,
        ], [
            'file_id' => $fileId,
            'data' => $composer,
            'is_dev' => $isDev,
            'is_private' => $isPrivate,
        ]);

        return $version->created_at !== $version->updated_at;
    }

    public function initializePackage(Package $package): void
    {
        $repositoryProvider = $package->repositoryProvider;
        $versions = $repositoryProvider->getService()->fetchVersions($package, true);
    }

    public static function packageNameRules(): array
    {
        $regex = '#^[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9](([_.]?|-{0,2})[a-z0-9]+)*$#';

        return [
            'required',
            'string',
            'regex:'.$regex,
        ];
    }
}
