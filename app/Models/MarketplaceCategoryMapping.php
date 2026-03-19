<?php

namespace App\Models;

use App\Enums\MarketplaceChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceCategoryMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'external_category_id',
        'external_category_title',
        'category_id',
        'raw_payload',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => MarketplaceChannel::class,
            'external_category_id' => 'integer',
            'category_id' => 'integer',
            'raw_payload' => 'array',
            'last_seen_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
