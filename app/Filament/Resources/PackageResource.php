<?php

namespace App\Filament\Resources;

use App\Actions\Package\Create;
use App\Enums\Packages;
use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers\VersionsRelationManager;
use App\Models\Package;
use App\Models\RepositoryProvider;
use App\Models\StorageProvider;
use App\Models\Team;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'codicon-package';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'type'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Slug' => $record->slug,
            'Type' => $record->type,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if (Auth::user()->role->read_all_teams) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->whereIn('team_id', Auth::user()->teams()->pluck('id'));
    }

    public static function form(Form $form): Form
    {
        $creator = app(Create::class);

        $rules = $creator->rules(isEdit: $form->getRecord() !== null, package: $form->getRecord());

        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->rules($rules['name']),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->rule(fn (Rule $rule, Get $get) => $rule->unique(Package::class, 'slug')->where('team_id', $get('team_id'))->ignore($get('id')))
                    ->rules(function (Get $get) use ($rules) {
                        $type = $get('type');

                        if (! $type) {
                            return [];
                        }

                        $packageProvider = Packages::from($type);

                        $providerRules = $packageProvider->getService()::packageNameRules();

                        return array_merge($providerRules, $rules['slug']);
                    }),
                Select::make('team_id')
                    ->options(function () {
                        if (Auth::user()->role->write_all_teams) {
                            return Team::all()->pluck('name', 'id');
                        }

                        return Auth::user()->teams()->where('write', true)->pluck('name', 'id');
                    })
                    ->required()
                    ->rules($rules['team_id']),
                Section::make('Visibility')
                    ->schema([
                        Select::make('is_private')
                            ->options([
                                true => 'Private',
                                false => 'Public',
                            ])
                            ->default(false)
                            ->rules($rules['is_private']),
                        Select::make('versions_are_private_by_default')
                            ->options([
                                true => 'Yes',
                                false => 'No',
                            ])
                            ->rules($rules['versions_are_private_by_default']),
                    ])
                    ->columns(2),
                Select::make('type')
                    ->options(Packages::class)
                    ->required()
                    ->disabledOn('edit')
                    ->live()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set) {
                        $set('repository_provider_id', null);
                    })
                    ->rules($rules['type']),
                Select::make('repository_provider_id')
                    ->options(RepositoryProvider::all()->pluck('name', 'id'))
                    ->live()
                    ->reactive()
                    ->disabledOn('edit')
                    ->visible(fn (Get $get) => $get('type') && app(Packages::from($get('type'))->getService())->getRequiresRepository())
                    ->rules($rules['repository_provider_id']),
                Select::make('storage_provider_id')
                    ->options(StorageProvider::all()->pluck('name', 'id'))
                    ->required()
                    ->disabledOn('edit')
                    ->rules($rules['storage_provider_id']),
                Section::make('Configuration')
                    ->schema(function (Get $get) {
                        $repositoryProviderId = $get('repository_provider_id');

                        if (! $repositoryProviderId) {
                            return [];
                        }

                        $repositoryProvider = RepositoryProvider::find($repositoryProviderId);

                        return $repositoryProvider->getService()::createPackageForm();
                    })
                    ->visible(function (Get $get) {
                        $repositoryProviderId = $get('repository_provider_id');

                        if (! $repositoryProviderId) {
                            return false;
                        }

                        $repositoryProvider = RepositoryProvider::find($repositoryProviderId);

                        return $repositoryProvider->getService()::createPackageForm() !== [];
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('slug'),
                TextColumn::make('type'),
                TextColumn::make('repositoryProvider.name'),
                ToggleColumn::make('is_private')->disabled(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordAction(ViewAction::class);
    }

    public static function getRelations(): array
    {
        return [
            VersionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
            'view' => Pages\ViewPackage::route('/{record}'),
        ];
    }
}
