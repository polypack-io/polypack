<?php

namespace App\Filament\Resources\PackageResource\RelationManagers;

use App\Models\PackageVersion;
use App\Policies\PackageVersionPolicy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('version')
                    ->required()
                    ->maxLength(255)
                    ->disabledOn('edit'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version')
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->label('Version')
                    ->icon(function (PackageVersion $record) {
                        return match ($record->is_dev) {
                            true => 'codicon-debug',
                            false => 'codicon-package',
                        };
                    }),
                Tables\Columns\ToggleColumn::make('is_private')
                    ->label('Private')
                    ->disabled(function (PackageVersion $record) {
                        return ! app(PackageVersionPolicy::class)->update(Auth::user(), $record);
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d-m-Y H:i'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d-m-Y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize(function (PackageVersion $record) {
                        return app(PackageVersionPolicy::class)->update(Auth::user(), $record);
                    }),
                Tables\Actions\DeleteAction::make()
                    ->authorize(function (PackageVersion $record) {
                        return app(PackageVersionPolicy::class)->delete(Auth::user(), $record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
