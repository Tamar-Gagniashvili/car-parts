<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ძირითადი ინფორმაცია')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('დასახელება')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('არასავალდებულო. თუ მითითებულია, უნიკალური უნდა იყოს.'),
                        Select::make('category_id')
                            ->label('კატეგორია')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Toggle::make('is_active')
                            ->label('აქტიურია')
                            ->default(true),
                        Textarea::make('description')
                            ->label('აღწერა')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Section::make('ფასები')
                    ->columns(3)
                    ->schema([
                        TextInput::make('cost_price')
                            ->label('თვითღირებულება')
                            ->numeric()
                            ->prefix('₾')
                            ->helperText('არასავალდებულო.'),
                        TextInput::make('sale_price')
                            ->label('გასაყიდი ფასი')
                            ->numeric()
                            ->prefix('₾')
                            ->helperText('არასავალდებულო.'),
                        TextInput::make('currency_id')
                            ->label('ვალუტა (ID)')
                            ->numeric()
                            ->helperText('არასავალდებულო (დროებითი ველი).'),
                    ]),

                Section::make('მარაგი')
                    ->columns(3)
                    ->schema([
                        TextInput::make('quantity_in_stock')
                            ->label('რაოდენობა მარაგში')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->helperText('ამჟამინდელი რაოდენობა (შემდგომში მოძრაობებით იმართება).'),
                        TextInput::make('condition_type_id')
                            ->label('მდგომარეობა (ID)')
                            ->numeric()
                            ->helperText('არასავალდებულო (დროებითი ველი).'),
                        TextInput::make('location_label')
                            ->label('ლოკაცია')
                            ->maxLength(255),
                    ]),

                Section::make('შენიშვნები')
                    ->schema([
                        Textarea::make('notes')
                            ->label('შენიშვნები')
                            ->rows(4),
                    ]),
            ]);
    }
}
