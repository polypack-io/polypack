<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Package;
use App\Models\PackageVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AccessRelationManager extends RelationManager
{
    protected static string $relationship = 'access';

    public function getTabs(): array
    {
        return [
            'packages' => Tab::make('Packages')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->where('accessable_type', Package::class);
                }),
            'versions' => Tab::make('Versions')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->where('accessable_type', PackageVersion::class);
                }),
        ];
    }

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
                Tables\Columns\TextColumn::make('accessable.name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->authorize(Auth::user()->role->manage_clients)
                    ->form([
                        Forms\Components\Select::make('accessable_type')
                            ->options([
                                Package::class => 'Package',
                                PackageVersion::class => 'Version',
                            ])
                            ->live()
                            ->reactive()
                            ->required(),
                        Forms\Components\Select::make('accessable_id')
                            ->options(function (Get $get) {
                                $accessableType = $get('accessable_type');

                                if ($accessableType === Package::class) {
                                    return Package::query()->where('is_private', true)->pluck('name', 'id');
                                }

                                if ($accessableType === PackageVersion::class) {
                                    $versions = PackageVersion::query()->where('is_private', true)->get();

                                    $data = [];

                                    foreach ($versions as $version) {
                                        $data[$version->id] = $version->name;
                                    }

                                    return $data;
                                }
                            })
                            ->visible(fn (Get $get) => $get('accessable_type'))
                            ->live()
                            ->reactive()
                            ->required()
                            ->searchable(),
                    ]),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->authorize(Auth::user()->role->manage_clients),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->authorize(Auth::user()->role->manage_clients),
                ]),
            ]);
    }
}
