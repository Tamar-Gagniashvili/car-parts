<?php

namespace App\Filament\Resources\MarketplaceListings\Tables;

use App\Enums\MarketplaceChannel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MarketplaceListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('channel')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $value = $state instanceof MarketplaceChannel ? $state->value : (string) $state;

                        return MarketplaceChannel::from($value)->label();
                    })
                    ->label('არხი')
                    ->sortable(),
                TextColumn::make('external_id')
                    ->label('გარე ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('შიდა პროდუქტი')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('external_price')
                    ->label('ფასი')
                    ->money('GEL')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('external_quantity')
                    ->label('რაოდენობა')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('views')
                    ->label('ნახვები')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('create_date')
                    ->label('შექმნილია')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('update_date')
                    ->label('განახლდა')
                    ->dateTime()
                    ->since()
                    ->toggleable(),
                TextColumn::make('end_date')
                    ->label('დასრულდება')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_synced_at')
                    ->dateTime()
                    ->since()
                    ->label('სინქრონიზაცია')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('არხი')
                    ->options(MarketplaceChannel::options()),
                SelectFilter::make('product_id')
                    ->label('მიბმულია პროდუქტზე')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
