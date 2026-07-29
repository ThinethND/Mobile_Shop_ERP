<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cosmetic_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brand_id')->constrained('cosmetic_brands');
            $table->foreignId('category_id')->constrained('cosmetic_categories');
            $table->foreignId('product_type_id')->constrained('cosmetic_product_types');
            $table->foreignId('country_of_origin_id')->constrained('cosmetic_countries_of_origin');

            $table->string('name');

            $table->json('size_volume_ids')->nullable();

            $table->string('batch_number')->nullable();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock')->nullable();

            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('hot_deals')->default(false);
            $table->boolean('best_selling')->default(false);

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();

            $table->string('main_image_path')->nullable();
            $table->json('gallery_image_paths')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cosmetic_products');
    }
};

