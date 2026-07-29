<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cosmetic_size_volumes', function (Blueprint $table) {
            $table->id();
            $table->decimal('size', 10, 2);
            $table->string('unit', 10);
            $table->timestamps();

            $table->unique(['size', 'unit'], 'cosmetic_size_volume_unique_combo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cosmetic_size_volumes');
    }
};

