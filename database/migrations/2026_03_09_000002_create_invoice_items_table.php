<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->unsignedInteger('item_no')->default(1);

            $table->string('product_type'); // tech, shoe
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('model_name')->nullable();
            $table->string('storage')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('imei_serial')->nullable();
            $table->string('warranty')->nullable();
            $table->boolean('is_preorder')->default(false);

            $table->text('description');

            $table->unsignedInteger('qty')->default(1);

            $table->decimal('regular_price', 12, 2)->default(0);
            $table->string('discount_type')->nullable(); // percentage, fixed
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->decimal('discount_percent_display', 8, 2)->nullable();
            $table->decimal('discounted_unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['invoice_id', 'item_no']);
            $table->index(['product_type', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};