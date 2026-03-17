<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVehicleFit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'manufacturer_external_id',
        'model_external_id',
        'year_from',
        'year_to',
        'volume',
        'is_main',
    ];

    protected function casts(): array
    {
        return [
            'manufacturer_external_id' => 'integer',
            'model_external_id' => 'integer',
            'year_from' => 'integer',
            'year_to' => 'integer',
            'is_main' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
