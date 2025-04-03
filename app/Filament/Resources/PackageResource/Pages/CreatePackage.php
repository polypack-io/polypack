<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Actions\Package\Create as CreatePackageAction;
use App\Filament\Resources\PackageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePackage extends CreateRecord
{
    protected static string $resource = PackageResource::class;

    public function create(bool $another = false): void
    {
        $creator = app(CreatePackageAction::class);
        $package = $creator->create($this->form->getState());

        if ($another) {
            $this->redirect(PackageResource::getUrl('create'));
        }

        $this->redirect(PackageResource::getUrl('index'));
    }
}
