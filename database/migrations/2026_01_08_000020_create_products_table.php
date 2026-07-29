<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();

            $table->string('model');
            $table->enum('device_status', ['used', 'brandnew'])->default('brandnew');
            $table->decimal('price_lkr', 12, 2);

            $table->enum('discount_type', ['percent', 'price'])->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();

            $table->boolean('in_stock')->default(true);
            $table->unsignedInteger('stock_count')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_featured')->default(false);

            $table->foreignId('warranty_option_id')->nullable()->constrained('warranty_options')->nullOnDelete();
            $table->string('warranty_period')->nullable();

            // optional specs
            $table->string('os')->nullable();
            $table->foreignId('storage_option_id')->nullable()->constrained('storage_options')->nullOnDelete();
            $table->foreignId('ram_option_id')->nullable()->constrained('ram_options')->nullOnDelete();

            $table->string('display_size')->nullable();
            $table->string('display_type')->nullable();
            $table->string('resolution')->nullable();
            $table->string('rear_camera')->nullable();
            $table->string('front_camera')->nullable();
            $table->string('connectivity')->nullable();
            $table->unsignedInteger('battery_mah')->nullable();

            // descriptions
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();

            // images
            $table->string('main_image_path')->nullable();
            $table->json('gallery_image_paths')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
