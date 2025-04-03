<?php

namespace App\Enums;

use App\Contracts\PackageProvider;
use App\Services\PackageServices\ComposerService;
use App\Services\PackageServices\NPMService;
use Filament\Support\Contracts\HasLabel;

enum Packages: string implements HasLabel
{
    case COMPOSER = 'composer';
    case NPM = 'npm';

    public function getLabel(): string
    {
        return match ($this) {
            self::COMPOSER => 'Composer',
            self::NPM => 'NPM',
        };
    }

    /**
     * @return class-string<PackageProvider>
     */
    public function getService(): string
    {
        return match ($this) {
            self::COMPOSER => ComposerService::class,
            self::NPM => NPMService::class,
        };
    }
}
