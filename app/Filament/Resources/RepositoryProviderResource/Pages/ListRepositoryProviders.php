<?php

namespace App\Filament\Resources\RepositoryProviderResource\Pages;

use App\Filament\Resources\RepositoryProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepositoryProviders extends ListRecords
{
    protected static string $resource = RepositoryProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
