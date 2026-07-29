<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('value');
            $table->string('unit', 2); // GB / TB
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['value', 'unit']); // prevent duplicates like 128 GB twice
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_options');
    }
};
