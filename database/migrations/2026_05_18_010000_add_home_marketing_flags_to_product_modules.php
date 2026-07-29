<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['motorcycle_products', 'fashion_products', 'home_need_products'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'today_best_deals')) {
                    $table->boolean('today_best_deals')->default(false)->after('featured');
                }

                if (!Schema::hasColumn($tableName, 'best_seller')) {
                    $table->boolean('best_seller')->default(false)->after('today_best_deals');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['motorcycle_products', 'fashion_products', 'home_need_products'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'best_seller')) {
                    $table->dropColumn('best_seller');
                }

                if (Schema::hasColumn($tableName, 'today_best_deals')) {
                    $table->dropColumn('today_best_deals');
                }
            });
        }
    }
};
