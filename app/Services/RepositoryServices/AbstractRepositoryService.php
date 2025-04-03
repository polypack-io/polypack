<?php

namespace App\Services\RepositoryServices;

use App\Contracts\RepositoryProvider;

abstract class AbstractRepositoryService implements RepositoryProvider
{
    protected ?array $credentials;

    public function __construct(?array $credentials = null)
    {
        $this->credentials = $credentials;
    }
}
