<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ClientsRelationManager extends RelationManager
{
    protected static string $relationship = 'clients';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->authorize(Auth::user()->role->manage_clients),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->authorize(Auth::user()->role->manage_clients),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->authorize(Auth::user()->role->manage_clients),
                ]),
            ]);
    }
}
