<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'category_id',
        'condition_type_id',
        'quantity_in_stock',
        'cost_price',
        'sale_price',
        'currency_id',
        'phone',
        'location_label',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'quantity_in_stock' => 'integer',
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'condition_type_id' => 'integer',
            'currency_id' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function vehicleFits(): HasMany
    {
        return $this->hasMany(ProductVehicleFit::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function marketplaceListings(): HasMany
    {
        return $this->hasMany(MarketplaceListing::class);
    }
}
