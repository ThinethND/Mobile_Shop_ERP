<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cosmetic_product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cosmetic_product_id')
                ->constrained('cosmetic_products')
                ->cascadeOnDelete();

            $table->foreignId('cosmetic_size_volume_id')
                ->constrained('cosmetic_size_volumes')
                ->cascadeOnDelete();

            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock_count')->nullable();
            $table->string('sku')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->unique(
                ['cosmetic_product_id', 'cosmetic_size_volume_id'],
                'cosmetic_product_variant_unique_combo'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cosmetic_product_variants');
    }
};

