<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_no')->unique();
            $table->date('invoice_date');

            $table->string('customer_name');
            $table->string('customer_contact_number');
            $table->string('customer_address')->nullable();
            $table->string('customer_email')->nullable();

            $table->string('sales_person')->nullable();
            $table->date('ship_date')->nullable();
            $table->string('ship_via')->nullable();

            $table->string('payment_type')->nullable(); // cash, card
            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_discount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            $table->string('status')->default('draft'); // draft, finalized, cancelled
            $table->string('pdf_path')->nullable();

            $table->timestamps();

            $table->index(['invoice_no', 'status']);
            $table->index(['customer_name', 'customer_contact_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};