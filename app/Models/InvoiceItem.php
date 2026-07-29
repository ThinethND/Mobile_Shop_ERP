<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_no',
        'product_type',
        'product_id',
        'model_name',
        'storage',
        'color',
        'size',
        'imei_serial',
        'warranty',
        'is_preorder',
        'description',
        'qty',
        'regular_price',
        'discount_type',
        'discount_value',
        'discount_percent_display',
        'discounted_unit_price',
        'line_total',
    ];

    protected $casts = [
        'is_preorder' => 'boolean',
        'qty' => 'integer',
        'regular_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_percent_display' => 'decimal:2',
        'discounted_unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}