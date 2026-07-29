<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('storage_option_id')
                ->constrained('storage_options')
                ->cascadeOnDelete();

            $table->foreignId('color_option_id')
                ->constrained('color_options')
                ->cascadeOnDelete();

            $table->decimal('price_lkr', 12, 2);
            $table->unsignedInteger('stock_count')->nullable();
            $table->string('sku')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->unique(
                ['product_id', 'storage_option_id', 'color_option_id'],
                'product_variant_unique_combo'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};