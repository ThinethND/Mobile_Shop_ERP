<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shoe_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brand_id')->constrained('shoe_brands');
            $table->foreignId('product_type_id')->constrained('shoe_types');
            $table->foreignId('category_id')->constrained('shoes_categories');
            $table->foreignId('subcategory_id')->constrained('shoe_subcategories');

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('sku')->nullable()->index();
            $table->string('barcode')->nullable()->index();
            $table->string('model_code')->nullable();

            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();

            $table->enum('status', ['draft', 'published', 'out_of_stock', 'archived'])->default('draft');

            $table->boolean('featured')->default(false);
            $table->boolean('new_arrival')->default(false);
            $table->boolean('best_seller')->default(false);

            $table->decimal('regular_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();

            $table->string('currency', 20)->default('LKR');
            $table->string('tax_class', 100)->nullable();

            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();

            $table->date('sale_start_date')->nullable();
            $table->date('sale_end_date')->nullable();

            $table->unsignedInteger('stock_quantity')->nullable();
            $table->unsignedInteger('low_stock_alert_quantity')->nullable();
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'preorder'])->default('in_stock');

            $table->enum('gender', ['men', 'women', 'kids', 'unisex'])->nullable();
            $table->enum('age_group', ['adult', 'teen', 'kids', 'baby'])->nullable();

            $table->json('size_type_ids')->nullable();
            $table->json('sizes_by_type')->nullable();
            $table->json('color_ids')->nullable();
            $table->json('material_ids')->nullable();

            $table->string('shoe_weight', 100)->nullable();

            $table->string('thumbnail_path')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('hover_image_path')->nullable();

            $table->string('product_video_url', 2048)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoe_products');
    }
};