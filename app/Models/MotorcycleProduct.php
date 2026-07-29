<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MotorcycleProduct extends Model
{
    use HasFactory;

    public const TYPES = [
        'helmet',
        'helmet_accessory',
        'bike_accessory',
        'spare_part',
    ];

    protected $fillable = [
        'name',
        'slug',
        'product_type',
        'category_id',
        'helmet_brand_id',
        'compatible_helmet_brand_id',
        'compatible_bike_brand_id',
        'compatible_bike_model_id',
        'short_description',
        'full_description',
        'main_image_path',
        'hover_image_path',
        'product_video_url',
        'gallery_image_paths',
        'regular_price',
        'sale_price',
        'cost_price',
        'sku',
        'stock_quantity',
        'low_stock_alert_quantity',
        'stock_status',
        'status',
        'featured',
        'today_best_deals',
        'best_seller',
        'warranty_option_id',
        'warranty_period',
        'warranty',
        'specifications',
    ];

    protected $casts = [
        'gallery_image_paths' => 'array',
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_alert_quantity' => 'integer',
        'featured' => 'boolean',
        'today_best_deals' => 'boolean',
        'best_seller' => 'boolean',
        'specifications' => 'array',
    ];

    protected $appends = [
        'main_image_url',
        'hover_image_url',
        'gallery_urls',
    ];

    public function category()
    {
        return $this->belongsTo(MotorcycleProductCategory::class, 'category_id');
    }

    public function helmetBrand()
    {
        return $this->belongsTo(MotorcycleHelmetBrand::class, 'helmet_brand_id');
    }

    public function compatibleHelmetBrand()
    {
        return $this->belongsTo(MotorcycleHelmetBrand::class, 'compatible_helmet_brand_id');
    }

    public function compatibleBikeBrand()
    {
        return $this->belongsTo(MotorcycleBikeBrand::class, 'compatible_bike_brand_id');
    }

    public function compatibleBikeModel()
    {
        return $this->belongsTo(MotorcycleBikeModel::class, 'compatible_bike_model_id');
    }

    public function reviews()
    {
        return $this->hasMany(MotorcycleProductReview::class, 'product_id');
    }

    public function warrantyOption()
    {
        return $this->belongsTo(WarrantyOption::class);
    }

    public function getMainImageUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->main_image_path);
    }

    public function getHoverImageUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->hover_image_path);
    }

    public function getGalleryUrlsAttribute(): array
    {
        return collect($this->gallery_image_paths ?? [])
            ->filter()
            ->map(fn ($path) => $this->publicFileUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    protected function publicFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
