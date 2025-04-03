<?php

namespace App\Enums;

use App\Services\RepositoryServices\GitHubService;
use Filament\Support\Contracts\HasLabel;

enum Repositories: string implements HasLabel
{
    case GITHUB = 'github';

    public function getLabel(): string
    {
        return match ($this) {
            self::GITHUB => 'GitHub',
        };
    }

    public function getService(): string
    {
        return match ($this) {
            self::GITHUB => GitHubService::class,
        };
    }
}
