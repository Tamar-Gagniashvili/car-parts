<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\Currency;
use App\Enums\MarketplaceChannel;
use App\Support\MoneyFormatter;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MarketplaceListingsRelationManager extends RelationManager
{
    protected static string $relationship = 'marketplaceListings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('channel')
                ->label('არხი')
                ->options(MarketplaceChannel::options())
                ->required()
                ->searchable()
                ->preload(),
            TextInput::make('external_id')
                ->label('გარე ID')
                ->required()
                ->maxLength(255),
            TextInput::make('external_price')
                ->label('ფასი')
                ->numeric(),
            TextInput::make('external_quantity')
                ->label('რაოდენობა')
                ->numeric(),
            TextInput::make('views')
                ->label('ნახვები')
                ->numeric(),
        ]);
    }

    public function table(Table $table): Table
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
                TextColumn::make('external_id')->label('გარე ID')->searchable()->sortable(),
                TextColumn::make('external_price')
                    ->label('ფასი')
                    ->formatStateUsing(fn ($state, $record) => MoneyFormatter::format(
                        amount: $state !== null ? (float) $state : null,
                        currency: Currency::fromId($record->external_currency_id),
                    ))
                    ->sortable(),
                TextColumn::make('external_quantity')->label('რაოდენობა')->sortable(),
                TextColumn::make('views')->label('ნახვები')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('update_date')->dateTime()->since()->label('განახლდა')->sortable()->toggleable(),
                TextColumn::make('last_synced_at')->dateTime()->since()->label('სინქრონიზაცია')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()->iconButton(),
            ])
            ->actions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->emptyStateHeading('მონაცემები ვერ მოიძებნა')
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
