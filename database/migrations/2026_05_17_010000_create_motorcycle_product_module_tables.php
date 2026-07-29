<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motorcycle_product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_category_id')
                ->nullable()
                ->constrained('motorcycle_product_categories')
                ->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['name', 'parent_category_id'], 'motorcycle_categories_name_parent_unique');
        });

        Schema::create('motorcycle_helmet_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('logo_path')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('motorcycle_bike_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('logo_path')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('motorcycle_bike_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bike_brand_id')
                ->constrained('motorcycle_bike_brands')
                ->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->unsignedSmallInteger('engine_cc')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['bike_brand_id', 'name']);
        });

        Schema::create('motorcycle_product_options', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->string('name');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['type', 'name'], 'motorcycle_options_type_name_unique');
        });

        Schema::create('motorcycle_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('product_type', 40);
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('motorcycle_product_categories')
                ->nullOnDelete();
            $table->foreignId('helmet_brand_id')
                ->nullable()
                ->constrained('motorcycle_helmet_brands')
                ->nullOnDelete();
            $table->foreignId('compatible_helmet_brand_id')
                ->nullable()
                ->constrained('motorcycle_helmet_brands')
                ->nullOnDelete();
            $table->foreignId('compatible_bike_brand_id')
                ->nullable()
                ->constrained('motorcycle_bike_brands')
                ->nullOnDelete();
            $table->foreignId('compatible_bike_model_id')
                ->nullable()
                ->constrained('motorcycle_bike_models')
                ->nullOnDelete();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('main_image_path')->nullable();
            $table->json('gallery_image_paths')->nullable();
            $table->decimal('regular_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->string('sku')->nullable()->unique();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_alert_quantity')->nullable();
            $table->string('stock_status', 30)->default('in_stock');
            $table->string('status', 20)->default('active');
            $table->boolean('featured')->default(false);
            $table->string('warranty')->nullable();
            $table->json('specifications')->nullable();
            $table->timestamps();

            $table->index(['product_type', 'status']);
            $table->index(['category_id', 'product_type']);
        });

        Schema::create('motorcycle_product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('motorcycle_products')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->json('image_paths')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motorcycle_product_reviews');
        Schema::dropIfExists('motorcycle_products');
        Schema::dropIfExists('motorcycle_product_options');
        Schema::dropIfExists('motorcycle_bike_models');
        Schema::dropIfExists('motorcycle_bike_brands');
        Schema::dropIfExists('motorcycle_helmet_brands');
        Schema::dropIfExists('motorcycle_product_categories');
    }
};
