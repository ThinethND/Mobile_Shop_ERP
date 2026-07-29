<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'storage_option_id',
        'color_option_id',
        'price_lkr',
        'stock_count',
        'sku',
        'status',
    ];

    protected $casts = [
        'price_lkr' => 'decimal:2',
        'stock_count' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function storageOption()
    {
        return $this->belongsTo(StorageOption::class);
    }

    public function colorOption()
    {
        return $this->belongsTo(ColorOption::class);
    }
}