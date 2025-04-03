<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Actions\User\CreateUser as UserCreateAction;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $createUser = app(UserCreateAction::class);

        return $createUser->create($data);
    }
}
