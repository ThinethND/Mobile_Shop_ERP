<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'sku',
        'slug',
        'model',
        'device_status',
        'price_lkr',

        'discount_type',
        'discount_value',

        'in_stock',
        'stock_count',
        'low_stock_alert_quantity',

        'status',
        'is_featured',
        'is_best_seller',
        'is_top_rated',
        'is_pre_order',
        'is_deal_of_the_day',
        'is_coming_soon',

        'warranty_option_id',
        'warranty_period',

        'os',

        'storage_option_ids',
        'ram_option_ids',
        'color_ids',

        // legacy
        'storage_option_id',
        'ram_option_id',

        'display_size',
        'display_type',
        'resolution',
        'rear_camera',
        'front_camera',
        'connectivity',
        'battery_mah',

        'short_description',
        'long_description',

        'main_image_path',
        'hover_image_path',
        'gallery_image_paths',
        'product_video_url',
    ];

    protected $casts = [
        'in_stock' => 'boolean',
        'is_featured' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_top_rated' => 'boolean',
        'is_pre_order' => 'boolean',
        'is_deal_of_the_day' => 'boolean',
        'is_coming_soon' => 'boolean',

        'gallery_image_paths' => 'array',
        'storage_option_ids' => 'array',
        'ram_option_ids' => 'array',
        'color_ids' => 'array',
    ];

    protected $appends = [
        'main_image_url',
        'hover_image_url',
        'gallery_urls',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function warrantyOption()
    {
        return $this->belongsTo(WarrantyOption::class);
    }

    public function colors()
    {
        return $this->belongsToMany(ColorOption::class, 'color_option_product', 'product_id', 'color_option_id');
    }

    public function variants()
{
    return $this->hasMany(ProductVariant::class);
}

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function routeIdentifier(): string|int
    {
        return filled($this->slug) ? $this->slug : $this->id;
    }

    public function getMainImageUrlAttribute(): ?string
    {
        if (!$this->main_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->main_image_path);
    }

    public function getHoverImageUrlAttribute(): ?string
    {
        if (!$this->hover_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->hover_image_path);
    }

    public function getGalleryUrlsAttribute(): array
    {
        $paths = $this->gallery_image_paths ?: [];

        return array_values(array_map(
            fn ($p) => Storage::disk('public')->url($p),
            $paths
        ));
    }
}
