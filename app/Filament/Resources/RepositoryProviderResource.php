<?php

namespace App\Filament\Resources;

use App\Enums\Repositories;
use App\Filament\Resources\RepositoryProviderResource\Pages;
use App\Models\RepositoryProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepositoryProviderResource extends Resource
{
    protected static ?string $model = RepositoryProvider::class;

    protected static ?string $navigationIcon = 'codicon-repo-clone';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'type'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options(Repositories::class)
                    ->required()
                    ->live()
                    ->reactive()
                    ->disabledOn(['edit']),
                Forms\Components\Section::make('Configuration')
                    ->schema(function (Get $get) {
                        $type = $get('type');

                        if (! $type) {
                            return [];
                        }

                        return Repositories::from($type)->getService()::form();
                    })
                    ->visible(fn (Get $get) => $get('type') && Repositories::from($get('type'))->getService()::form() !== [])
                    ->live()
                    ->reactive(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => Repositories::from($state)->getLabel()),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListRepositoryProviders::route('/'),
            'create' => Pages\CreateRepositoryProvider::route('/create'),
            'edit' => Pages\EditRepositoryProvider::route('/{record}/edit'),
        ];
    }
}
