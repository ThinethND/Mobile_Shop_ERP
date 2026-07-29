<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shoe_products', function (Blueprint $table) {
            $table->dropForeign(['product_type_id']);
            $table->unsignedBigInteger('product_type_id')->nullable()->change();
            $table->foreign('product_type_id')
                ->references('id')
                ->on('shoe_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shoe_products', function (Blueprint $table) {
            $table->dropForeign(['product_type_id']);
            $table->unsignedBigInteger('product_type_id')->nullable(false)->change();
            $table->foreign('product_type_id')
                ->references('id')
                ->on('shoe_types')
                ->cascadeOnDelete();
        });
    }
};