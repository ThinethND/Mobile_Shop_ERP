<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('brand_id');

            $table->boolean('is_best_seller')->default(false)->after('is_featured');
            $table->boolean('is_top_rated')->default(false)->after('is_best_seller');
            $table->boolean('is_pre_order')->default(false)->after('is_top_rated');
            $table->boolean('is_deal_of_the_day')->default(false)->after('is_pre_order');
            $table->boolean('is_coming_soon')->default(false)->after('is_deal_of_the_day');

            $table->integer('low_stock_alert_quantity')->nullable()->after('stock_count');

            $table->string('hover_image_path')->nullable()->after('main_image_path');
            $table->string('product_video_url')->nullable()->after('hover_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);

            $table->dropColumn([
                'sku',
                'is_best_seller',
                'is_top_rated',
                'is_pre_order',
                'is_deal_of_the_day',
                'is_coming_soon',
                'low_stock_alert_quantity',
                'hover_image_path',
                'product_video_url',
            ]);
        });
    }
};