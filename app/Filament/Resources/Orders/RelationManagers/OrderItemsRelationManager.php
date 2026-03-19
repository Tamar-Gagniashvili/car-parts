<?php

namespace App\Filament\Resources\Orders\RelationManagers;

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

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('product_id')
                ->label('პროდუქტი (არასავალდებულო)')
                ->relationship('product', 'name')
                ->searchable()
                ->preload(),
            TextInput::make('product_name_snapshot')
                ->label('პროდუქტის დასახელება (სნეპშოტი)')
                ->required()
                ->maxLength(255)
                ->helperText('ინახება შეკვეთაში, რათა ისტორია არ დაიკარგოს, თუნდაც პროდუქტი შეიცვალოს.'),
            TextInput::make('unit_price')
                ->label('ერთეულის ფასი')
                ->numeric()
                ->required(),
            TextInput::make('quantity')
                ->label('რაოდენობა')
                ->numeric()
                ->required()
                ->default(1),
            TextInput::make('total_price')
                ->label('ჯამი')
                ->numeric()
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name_snapshot')
                    ->label('პოზიცია')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('unit_price')->label('ერთეული')->money('GEL')->alignEnd()->sortable(),
                TextColumn::make('quantity')->label('რაოდენობა')->alignEnd()->sortable(),
                TextColumn::make('total_price')->label('ჯამი')->money('GEL')->alignEnd()->sortable(),
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
