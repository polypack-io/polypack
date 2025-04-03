<?php

namespace App\Filament\Resources\RepositoryProviderResource\Pages;

use App\Actions\RepositoryProvider\Create;
use App\Filament\Resources\RepositoryProviderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRepositoryProvider extends CreateRecord
{
    protected static string $resource = RepositoryProviderResource::class;

    public function create(bool $another = false): void
    {
        $creator = app(Create::class);
        $provider = $creator->execute($this->form->getState());

        if ($another) {
            $this->redirect(RepositoryProviderResource::getUrl('create'));
        }

        $this->redirect(RepositoryProviderResource::getUrl('index'));
    }
}
