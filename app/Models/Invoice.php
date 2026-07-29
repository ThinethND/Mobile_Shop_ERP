<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'invoice_date',
        'customer_name',
        'customer_contact_number',
        'customer_address',
        'customer_email',
        'sales_person',
        'ship_date',
        'ship_via',
        'delivery_enabled',
        'delivery_method',
        'delivery_payment_status',
        'tracking_id',
        'delivery_agent',
        'delivery_amount',
        'payment_type',
        'cash_paid',
        'card_paid',
        'advance_amount',
        'paid_amount',
        'subtotal',
        'total_discount',
        'tax_amount',
        'grand_total',
        'balance_due',
        'notes',
        'terms',
        'status',
        'order_status',
        'pdf_path',
    ];

    protected $casts = [
        'invoice_date' => 'date:Y-m-d',
        'ship_date' => 'date:Y-m-d',
        'delivery_enabled' => 'boolean',
        'delivery_amount' => 'decimal:2',
        'cash_paid' => 'decimal:2',
        'card_paid' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    protected $appends = [
        'pdf_url',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('item_no');
    }

    public function getPdfUrlAttribute(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        return Storage::disk('public')->url($this->pdf_path);
    }
}
