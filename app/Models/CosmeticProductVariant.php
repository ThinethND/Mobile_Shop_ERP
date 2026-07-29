<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CosmeticProductVariant extends Model
{
    use HasFactory;

    protected $table = 'cosmetic_product_variants';

    protected $fillable = [
        'cosmetic_product_id',
        'cosmetic_size_volume_id',
        'price',
        'stock_count',
        'sku',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_count' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(CosmeticProduct::class, 'cosmetic_product_id');
    }

    public function sizeVolume()
    {
        return $this->belongsTo(CosmeticSizeVolume::class, 'cosmetic_size_volume_id');
    }
}

