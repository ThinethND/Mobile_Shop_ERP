<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'full_name',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'postal_code',
        'delivery_note',
        'shipping_method_code',
        'shipping_method_name',
        'shipping_fee',
        'subtotal',
        'grand_total',
        'currency',
        'status',
        'email_sent_at',
        'admin_email_sent_at',
        'meta',
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'email_sent_at' => 'datetime',
        'admin_email_sent_at' => 'datetime',
        'meta' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}