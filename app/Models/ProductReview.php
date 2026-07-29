<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductReview extends Model
{
    use HasFactory;

    protected $table = 'product_reviews';

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
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlsAttribute(): array
    {
        $images = $this->image_paths ?? [];

        return collect($images)
            ->filter()
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->filter()
            ->values()
            ->all();
    }
}
