<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogResource\Pages;
use App\Models\Log;
use App\Models\Package;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LogResource extends Resource
{
    protected static ?string $model = Log::class;

    protected static ?string $navigationIcon = 'codicon-history';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('package.name'),
                TextColumn::make('package.type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color('secondary'),
                TextColumn::make('message'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('package_id')
                    ->options(Package::all()->pluck('name', 'id')),
                SelectFilter::make('event')
                    ->options(Log::all()->pluck('event', 'event')),
            ])
            ->actions([
                //
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
            'index' => Pages\ListLogs::route('/'),
        ];
    }
}
