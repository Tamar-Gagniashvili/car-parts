<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleFitsRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicleFits';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('manufacturer_external_id')
                ->label('მწარმოებელი (ID)')
                ->numeric()
                ->required(),
            TextInput::make('model_external_id')
                ->label('მოდელი (ID)')
                ->numeric()
                ->required(),
            TextInput::make('year_from')
                ->label('წელი -დან')
                ->numeric(),
            TextInput::make('year_to')
                ->label('წელი -მდე')
                ->numeric(),
            TextInput::make('volume')
                ->label('ძრავის მოცულობა')
                ->maxLength(50),
            Checkbox::make('is_main')
                ->label('მთავარი შესაბამისობა')
                ->helperText('მონიშნავს მთავარ შესაბამისობას.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_main')->boolean()->label('მთავარი')->sortable(),
                TextColumn::make('manufacturer_external_id')->label('მწარმოებელი (ID)')->sortable(),
                TextColumn::make('model_external_id')->label('მოდელი (ID)')->sortable(),
                TextColumn::make('year_from')->label('დან')->sortable(),
                TextColumn::make('year_to')->label('მდე')->sortable(),
                TextColumn::make('volume')->toggleable(isToggledHiddenByDefault: true),
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
