<?php

namespace App\Filament\Resources\PersonalAccessTokenResource\Pages;

use App\Actions\PersonalAccessToken\Create;
use App\Filament\Resources\PersonalAccessTokenResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManagePersonalAccessTokens extends ManageRecords
{
    protected static string $resource = PersonalAccessTokenResource::class;

    private string $token = '';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function (array $data) {
                    $user = Auth::user();
                    $action = app(Create::class);
                    $this->token = $action->execute($user, $data);
                    $this->halt();
                })
                ->infolist(function (): array {
                    if ($this->token !== '' && $this->token !== '0') {
                        return [
                            TextEntry::make('token')
                                ->state($this->token)
                                ->label('Token')
                                ->copyable()
                                ->helperText('You can only see this token once. If you lose it, you will need to create a new one.'),
                        ];
                    }

                    return [];
                }),
        ];
    }
}
