<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'codicon-briefcase';

    protected static ?string $navigationGroup = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('level')
                    ->required()
                    ->default(99)
                    ->numeric()
                    ->minValue(Auth::user()->role->level + 1)
                    ->maxValue(99),
                Section::make('Permissions')
                    ->schema([
                        Checkbox::make('read_all_teams'),
                        Checkbox::make('write_all_teams'),
                        Checkbox::make('manage_users'),
                        Checkbox::make('manage_clients'),
                        Checkbox::make('manage_settings'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('level')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('read_all_teams')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state ? 'Yes' : 'No')
                    ->color(fn (string $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('write_all_teams')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state ? 'Yes' : 'No')
                    ->color(fn (string $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('manage_users')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state ? 'Yes' : 'No')
                    ->color(fn (string $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('manage_clients')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state ? 'Yes' : 'No')
                    ->color(fn (string $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('manage_settings')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state ? 'Yes' : 'No')
                    ->color(fn (string $state): string => $state ? 'success' : 'danger'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ])
            ->defaultSort('level', 'asc');
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
