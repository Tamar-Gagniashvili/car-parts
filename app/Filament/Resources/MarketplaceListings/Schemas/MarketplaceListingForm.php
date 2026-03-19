<?php

namespace App\Filament\Resources\MarketplaceListings\Schemas;

use App\Enums\MarketplaceChannel;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MarketplaceListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('განცხადება')
                    ->columns(2)
                    ->schema([
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
                        Select::make('product_id')
                            ->label('შიდა პროდუქტი (არასავალდებულო)')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('external_status_id')
                            ->label('გარე სტატუსი (ID)')
                            ->numeric(),
                        TextInput::make('external_category_title')
                            ->label('გარე კატეგორია')
                            ->maxLength(255),
                        TextInput::make('external_category_id')
                            ->label('გარე კატეგორია (ID)')
                            ->numeric(),
                    ]),

                Section::make('მარკეტპლეისის ველები')
                    ->columns(3)
                    ->schema([
                        TextInput::make('external_price')->label('ფასი')->numeric(),
                        TextInput::make('external_quantity')->label('რაოდენობა')->numeric(),
                        TextInput::make('views')->label('ნახვები')->numeric(),
                        TextInput::make('external_thumb_url')
                            ->label('მინიატურის URL')
                            ->maxLength(2048),
                        TextInput::make('external_large_url')
                            ->label('დიდი სურათის URL')
                            ->maxLength(2048),
                        DateTimePicker::make('create_date')->label('შექმნის თარიღი'),
                        DateTimePicker::make('update_date')->label('განახლების თარიღი'),
                        DateTimePicker::make('end_date')->label('დასრულების თარიღი'),
                        DateTimePicker::make('last_synced_at')->label('ბოლო სინქრონიზაცია')->columnSpanFull(),
                    ]),

                Section::make('სურათი')
                    ->schema([
                        ViewField::make('image_preview')
                            ->label('')
                            ->view('filament.components.external-image-preview')
                            ->viewData(fn (callable $get) => [
                                'thumbUrl' => $get('external_thumb_url'),
                                'largeUrl' => $get('external_large_url'),
                            ]),
                    ]),

                Section::make('MyParts ბმული')
                    ->schema([
                        TextEntry::make('external_url')
                            ->label('ბმული')
                            ->url(fn ($record) => $record?->external_url, shouldOpenInNewTab: true),
                    ]),

                Section::make('Raw payload')
                    ->collapsed()
                    ->schema([
                        Textarea::make('raw_payload')
                            ->label('ნედლი მონაცემები (JSON)')
                            ->rows(12)
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(static fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null),
                    ]),
            ]);
    }
}
