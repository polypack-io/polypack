<?php

namespace App\Actions\RepositoryProvider;

use App\Models\RepositoryProvider;

class Create
{
    public function execute(array $data): RepositoryProvider
    {
        $repositoryProvider = RepositoryProvider::create($data);

        return $repositoryProvider;
    }
}
