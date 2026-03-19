<?php

namespace App\Models;

use App\Enums\MarketplaceChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceListing extends Model
{
    use HasFactory;

    public function getExternalUrlAttribute(): ?string
    {
        if (! $this->external_id) {
            return null;
        }

        if (($this->channel instanceof MarketplaceChannel ? $this->channel : MarketplaceChannel::from($this->channel)) !== MarketplaceChannel::MyParts) {
            return null;
        }

        return "https://myparts.ge/ka/pr/{$this->external_id}";
    }

    protected $fillable = [
        'product_id',
        'channel',
        'external_id',
        'external_status_id',
        'external_category_id',
        'external_category_title',
        'external_price',
        'external_currency_id',
        'external_quantity',
        'external_thumb_url',
        'external_large_url',
        'views',
        'create_date',
        'update_date',
        'end_date',
        'raw_payload',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => MarketplaceChannel::class,
            'external_status_id' => 'integer',
            'external_category_id' => 'integer',
            'external_price' => 'decimal:2',
            'external_currency_id' => 'integer',
            'external_quantity' => 'integer',
            'views' => 'integer',
            'create_date' => 'datetime',
            'update_date' => 'datetime',
            'end_date' => 'datetime',
            'raw_payload' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
