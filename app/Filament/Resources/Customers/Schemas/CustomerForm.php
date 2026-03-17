<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('კლიენტი')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('სახელი')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('ტელეფონი')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('ელ-ფოსტა')
                            ->email()
                            ->maxLength(255),
                        Select::make('source')
                            ->label('წყარო')
                            ->options([
                                'Walk-in' => 'ადგილზე მოსული',
                                'Phone call' => 'ტელეფონით',
                                'MyParts.ge' => 'MyParts.ge',
                                'Facebook' => 'Facebook',
                                'Repeat customer' => 'მუდმივი კლიენტი',
                                'Other' => 'სხვა',
                            ])
                            ->searchable()
                            ->preload(),
                        Textarea::make('notes')
                            ->label('შენიშვნები')
                            ->columnSpanFull()
                            ->rows(4),
                    ]),
            ]);
    }
}
