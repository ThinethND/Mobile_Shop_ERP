<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FashionProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'product_type_id',
        'brand_id',
        'brand_name',
        'sku',
        'target_gender',
        'size_label',
        'color',
        'material',
        'style',
        'fit',
        'lens_type',
        'frame_material',
        'bag_size',
        'closure_type',
        'strap_type',
        'dimensions',
        'care_instructions',
        'warranty_option_id',
        'warranty_period',
        'price',
        'sale_price',
        'stock_quantity',
        'low_stock_alert_quantity',
        'stock_status',
        'status',
        'featured',
        'today_best_deals',
        'best_seller',
        'short_description',
        'full_description',
        'main_image_path',
        'hover_image_path',
        'product_video_url',
        'gallery_image_paths',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_alert_quantity' => 'integer',
        'featured' => 'boolean',
        'today_best_deals' => 'boolean',
        'best_seller' => 'boolean',
        'gallery_image_paths' => 'array',
    ];

    protected $appends = [
        'main_image_url',
        'hover_image_url',
        'gallery_urls',
    ];

    public function category()
    {
        return $this->belongsTo(FashionCategory::class, 'category_id');
    }

    public function productType()
    {
        return $this->belongsTo(FashionProductType::class, 'product_type_id');
    }

    public function brand()
    {
        return $this->belongsTo(FashionBrand::class, 'brand_id');
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
        return collect($this->gallery_image_paths ?: [])
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
