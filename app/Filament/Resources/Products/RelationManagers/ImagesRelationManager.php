<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('thumb_url')
                ->label('მინიატურის URL')
                ->required()
                ->url()
                ->maxLength(2048),
            TextInput::make('large_url')
                ->label('დიდი სურათის URL')
                ->required()
                ->url()
                ->maxLength(2048),
            TextInput::make('sort_order')
                ->label('სორტირება')
                ->numeric()
                ->helperText('უფრო დაბალი მნიშვნელობა იქნება პირველი.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->sortable()
                    ->label('სორტირება')
                    ->alignEnd(),
                ImageColumn::make('thumb_url')
                    ->label('სურათი')
                    ->square()
                    ->size(64),
                TextColumn::make('updated_at')->label('განახლდა')->since()->toggleable(isToggledHiddenByDefault: true),
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
