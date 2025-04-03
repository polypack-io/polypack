<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Actions\PersonalAccessToken\Create;
use App\Policies\ClientPolicy;
use Auth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TokensRelationManager extends RelationManager
{
    protected static string $relationship = 'tokens';

    private string $token = '';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('abilities')
                    ->separator(',')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->authorize(app(ClientPolicy::class)->update(Auth::user(), $this->ownerRecord))
                    ->using(function (array $data, $livewire) {
                        $action = app(Create::class);
                        $this->token = $action->execute($this->ownerRecord, $data);
                        $livewire->js(
                            'window.navigator.clipboard.writeText("'.$this->token.'");'
                        );

                        Notification::make()
                            ->title('Token created')
                            ->body('The token has been copied to your clipboard.')
                            ->success()
                            ->send();

                        return $this->ownerRecord;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->authorize(app(ClientPolicy::class)->update(Auth::user(), $this->ownerRecord)),
            ])
            ->bulkActions([
                //
            ]);
    }
}
