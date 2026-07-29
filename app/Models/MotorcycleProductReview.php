<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MotorcycleProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'rating',
        'customer_name',
        'customer_email',
        'short_description',
        'long_description',
        'image_paths',
        'status',
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
        return $this->belongsTo(MotorcycleProduct::class, 'product_id');
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->image_paths ?? [])
            ->filter()
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->filter()
            ->values()
            ->all();
    }
}
