<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cosmetic_product_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cosmetic_category_id')
                ->constrained('cosmetic_categories')
                ->cascadeOnDelete();

            $table->string('name');
            $table->timestamps();

            $table->unique(['cosmetic_category_id', 'name'], 'cosmetic_product_types_unique_per_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cosmetic_product_types');
    }
};

