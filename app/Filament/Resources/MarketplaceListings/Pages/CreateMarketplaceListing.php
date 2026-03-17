<?php

namespace App\Filament\Resources\MarketplaceListings\Pages;

use App\Filament\Resources\MarketplaceListings\MarketplaceListingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketplaceListing extends CreateRecord
{
    protected static string $resource = MarketplaceListingResource::class;
}
