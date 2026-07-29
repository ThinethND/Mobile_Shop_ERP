<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['cosmetic_products', 'motorcycle_products', 'fashion_products', 'home_need_products'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'hover_image_path')) {
                    $table->string('hover_image_path')->nullable();
                }

                if (!Schema::hasColumn($tableName, 'product_video_url')) {
                    $table->string('product_video_url', 2048)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['cosmetic_products', 'motorcycle_products', 'fashion_products', 'home_need_products'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'product_video_url')) {
                    $table->dropColumn('product_video_url');
                }

                if (Schema::hasColumn($tableName, 'hover_image_path')) {
                    $table->dropColumn('hover_image_path');
                }
            });
        }
    }
};
