<?php

namespace App\Filament\Resources;

use App\Enums\Storage;
use App\Filament\Resources\StorageProviderResource\Pages;
use App\Models\StorageProvider;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StorageProviderResource extends Resource
{
    protected static ?string $model = StorageProvider::class;

    protected static ?string $navigationIcon = 'codicon-save';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options(Storage::class)
                    ->required()
                    ->live()
                    ->reactive(),
                Section::make('Configuration')
                    ->schema(function (Get $get) {
                        $type = $get('type');

                        if ($type) {
                            return Storage::from($type)->getService()::form();
                        }

                        return [];
                    })
                    ->visible(fn (Get $get) => $get('type'))
                    ->live()
                    ->reactive(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorageProviders::route('/'),
            'create' => Pages\CreateStorageProvider::route('/create'),
            'edit' => Pages\EditStorageProvider::route('/{record}/edit'),
        ];
    }
}
