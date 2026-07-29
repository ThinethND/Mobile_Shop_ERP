<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ram_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('value');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['value']); // prevent duplicates like 8 GB twice
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ram_options');
    }
};
