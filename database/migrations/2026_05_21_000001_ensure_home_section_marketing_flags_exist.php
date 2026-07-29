<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addBooleanColumnIfMissing('products', 'is_deal_of_the_day');
        $this->addBooleanColumnIfMissing('products', 'is_best_seller');

        $this->addBooleanColumnIfMissing('cosmetic_products', 'hot_deals');
        $this->addBooleanColumnIfMissing('cosmetic_products', 'best_selling');

        foreach (['motorcycle_products', 'fashion_products', 'home_need_products'] as $tableName) {
            $this->addBooleanColumnIfMissing($tableName, 'today_best_deals');
            $this->addBooleanColumnIfMissing($tableName, 'best_seller');
        }
    }

    public function down(): void
    {
        // Repair migration only: do not drop marketing data on rollback.
    }

    private function addBooleanColumnIfMissing(string $tableName, string $columnName): void
    {
        if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName) {
            $table->boolean($columnName)->default(false);
        });
    }
};
