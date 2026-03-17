<?php

namespace App\Filament\Resources\MarketplaceListings;

use App\Filament\Resources\MarketplaceListings\Pages\CreateMarketplaceListing;
use App\Filament\Resources\MarketplaceListings\Pages\EditMarketplaceListing;
use App\Filament\Resources\MarketplaceListings\Pages\ListMarketplaceListings;
use App\Filament\Resources\MarketplaceListings\Schemas\MarketplaceListingForm;
use App\Filament\Resources\MarketplaceListings\Tables\MarketplaceListingsTable;
use App\Models\MarketplaceListing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MarketplaceListingResource extends Resource
{
    protected static ?string $model = MarketplaceListing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $modelLabel = 'მარკეტპლეისის განცხადება';

    protected static ?string $pluralModelLabel = 'მარკეტპლეისის განცხადებები';

    protected static ?string $navigationLabel = 'განცხადებები (MyParts)';

    public static function form(Schema $schema): Schema
    {
        return MarketplaceListingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketplaceListingsTable::configure($table);
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
            'index' => ListMarketplaceListings::route('/'),
            'create' => CreateMarketplaceListing::route('/create'),
            'edit' => EditMarketplaceListing::route('/{record}/edit'),
        ];
    }
}
