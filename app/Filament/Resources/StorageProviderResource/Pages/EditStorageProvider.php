<?php

namespace App\Filament\Resources\StorageProviderResource\Pages;

use App\Filament\Resources\StorageProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStorageProvider extends EditRecord
{
    protected static string $resource = StorageProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
