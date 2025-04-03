<?php

namespace App\Contracts;

use App\Models\Package;
use Illuminate\Database\Eloquent\Model;

interface RepositoryProvider
{
    public function __construct(?array $credentials = null);

    /**
     * @return array{
     *     url: string,
     *     type: string,
     *     file_id: string,
     * }
     */
    public function getRepositoryDownloadUrl(Package $package, array $data, string $type = 'zipball'): ?array;

    public function getRepositoryFile(Package $package, array $data): ?string;

    public function setupHook(Package $package): void;

    public function deleteHook(Package $package): void;

    public static function form(): array;

    public static function formValidation(?Model $model): array;

    public function fetchVersions(Package $package, bool $includeBranches = false): void;

    public static function createPackageForm(): array;

    public static function createPackageValidation(?Package $model): array;
}
