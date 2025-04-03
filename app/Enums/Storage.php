<?php

namespace App\Enums;

use App\Contracts\StorageProvider;
use App\Services\StorageServices\FileStorageService;
use Filament\Support\Contracts\HasLabel;

enum Storage: string implements HasLabel
{
    case FILE = 'file';

    public function getLabel(): string
    {
        return match ($this) {
            self::FILE => 'File',
        };
    }

    /**
     * @return class-string<StorageProvider>
     */
    public function getService(): string
    {
        return match ($this) {
            self::FILE => FileStorageService::class,
        };
    }
}
