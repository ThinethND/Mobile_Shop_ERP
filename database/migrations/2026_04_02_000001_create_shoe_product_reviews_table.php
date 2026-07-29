<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shoe_product_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('shoe_products')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('rating')->nullable();

            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();

            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();

            $table->json('image_paths')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoe_product_reviews');
    }
};

