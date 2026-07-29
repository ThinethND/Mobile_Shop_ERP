<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ShoeProduct extends Model
{
    use HasFactory;

    protected $table = 'shoe_products';

    protected $fillable = [
        'name',
        'slug',
        'brand_id',
        'product_type_id',
        'category_id',
        'subcategory_id',
        'sku',
        'barcode',
        'model_code',
        'short_description',
        'full_description',
        'status',
        'featured',
        'new_arrival',
        'best_seller',
        'regular_price',
        'sale_price',
        'cost_price',
        'currency',
        'tax_class',
        'discount_type',
        'discount_value',
        'sale_start_date',
        'sale_end_date',
        'stock_quantity',
        'low_stock_alert_quantity',
        'stock_status',
        'gender',
        'age_group',
        'size_type_ids',
        'sizes_by_type',
        'color_ids',
        'material_ids',
        'shoe_weight',
        'thumbnail_path',
        'gallery_images',
        'hover_image_path',
        'product_video_url',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'new_arrival' => 'boolean',
        'best_seller' => 'boolean',
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'sale_start_date' => 'date:Y-m-d',
        'sale_end_date' => 'date:Y-m-d',
        'stock_quantity' => 'integer',
        'low_stock_alert_quantity' => 'integer',
        'size_type_ids' => 'array',
        'sizes_by_type' => 'array',
        'color_ids' => 'array',
        'material_ids' => 'array',
        'gallery_images' => 'array',
    ];

    protected $appends = [
        'thumbnail_url',
        'hover_image_url',
        'gallery_urls',
    ];

    public function brand()
    {
        return $this->belongsTo(ShoeBrand::class, 'brand_id');
    }

    public function productType()
    {
        return $this->belongsTo(ShoeType::class, 'product_type_id');
    }

    public function category()
    {
        return $this->belongsTo(ShoeCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(ShoeSubcategory::class, 'subcategory_id');
    }

    public function reviews()
    {
        return $this->hasMany(ShoeProductReview::class, 'product_id');
    }

    protected function publicFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#^public/#', '', $path);
        $path = ltrim($path, '/');

        return asset('storage/' . $path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->thumbnail_path);
    }

    public function getHoverImageUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->hover_image_path);
    }

    public function getGalleryUrlsAttribute(): array
    {
        $images = $this->gallery_images ?? [];

        return collect($images)
            ->filter()
            ->map(fn ($path) => $this->publicFileUrl($path))
            ->filter()
            ->values()
            ->all();
    }
}
