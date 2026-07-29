<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotorcycleBikeModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'bike_brand_id',
        'name',
        'start_year',
        'end_year',
        'engine_cc',
        'status',
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'engine_cc' => 'integer',
    ];

    public function brand()
    {
        return $this->belongsTo(MotorcycleBikeBrand::class, 'bike_brand_id');
    }
}
