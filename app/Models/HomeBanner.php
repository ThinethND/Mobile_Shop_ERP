<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'video_path',
        'desktop_image_path',
        'mobile_image_path',
    ];

    protected $appends = [
        'video_url',
        'desktop_image_url',
        'mobile_image_url',
    ];

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video_path
            ? Storage::disk('public')->url($this->video_path)
            : null;
    }

    public function getDesktopImageUrlAttribute(): ?string
    {
        return $this->desktop_image_path
            ? Storage::disk('public')->url($this->desktop_image_path)
            : null;
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        return $this->mobile_image_path
            ? Storage::disk('public')->url($this->mobile_image_path)
            : null;
    }
}