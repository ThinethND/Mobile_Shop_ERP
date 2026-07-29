<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_need_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('home_need_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('home_need_categories')
                ->nullOnDelete();
            $table->string('brand_name')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('unit_label')->nullable();
            $table->string('material')->nullable();
            $table->string('color')->nullable();
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
            $table->index(['stock_status', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_need_products');
        Schema::dropIfExists('home_need_categories');
    }
};
