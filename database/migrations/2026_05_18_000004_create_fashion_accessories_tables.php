<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fashion_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('fashion_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('fashion_product_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('fashion_categories')
                ->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['category_id', 'name']);
            $table->index(['category_id', 'status']);
        });

        Schema::create('fashion_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('fashion_categories')
                ->nullOnDelete();
            $table->foreignId('product_type_id')
                ->nullable()
                ->constrained('fashion_product_types')
                ->nullOnDelete();
            $table->foreignId('brand_id')
                ->nullable()
                ->constrained('fashion_brands')
                ->nullOnDelete();
            $table->string('brand_name')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('target_gender', 50)->nullable();
            $table->string('size_label')->nullable();
            $table->string('color')->nullable();
            $table->string('material')->nullable();
            $table->string('style')->nullable();
            $table->string('fit')->nullable();
            $table->string('lens_type')->nullable();
            $table->string('frame_material')->nullable();
            $table->string('bag_size')->nullable();
            $table->string('closure_type')->nullable();
            $table->string('strap_type')->nullable();
            $table->string('dimensions')->nullable();
            $table->text('care_instructions')->nullable();
            $table->foreignId('warranty_option_id')
                ->nullable()
                ->constrained('warranty_options')
                ->nullOnDelete();
            $table->string('warranty_period')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_alert_quantity')->nullable();
            $table->string('stock_status', 30)->default('in_stock');
            $table->string('status', 20)->default('active');
            $table->boolean('featured')->default(false);
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('main_image_path')->nullable();
            $table->json('gallery_image_paths')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index(['product_type_id', 'status']);
            $table->index(['brand_id', 'status']);
            $table->index(['stock_status', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fashion_products');
        Schema::dropIfExists('fashion_product_types');
        Schema::dropIfExists('fashion_brands');
        Schema::dropIfExists('fashion_categories');
    }
};
