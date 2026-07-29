<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CosmeticProductReview extends Model
{
    use HasFactory;

    protected $table = 'cosmetic_product_reviews';

    protected $fillable = [
        'product_id',
        'invoice_no',
        'rating',
        'customer_name',
        'customer_email',
        'short_description',
        'long_description',
        'image_paths',
    ];

    protected $casts = [
        'rating' => 'integer',
        'image_paths' => 'array',
    ];

    protected $appends = [
        'image_urls',
    ];

    public function product()
    {
        return $this->belongsTo(CosmeticProduct::class, 'product_id');
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

    public function getImageUrlsAttribute(): array
    {
        $images = $this->image_paths ?? [];

        return collect($images)
            ->filter()
            ->map(fn ($path) => $this->publicFileUrl($path))
            ->filter()
            ->values()
            ->all();
    }
}
