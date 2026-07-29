<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motorcycle_products', function (Blueprint $table) {
            $table->foreignId('warranty_option_id')
                ->nullable()
                ->after('featured')
                ->constrained('warranty_options')
                ->nullOnDelete();
            $table->string('warranty_period')->nullable()->after('warranty_option_id');
        });
    }

    public function down(): void
    {
        Schema::table('motorcycle_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warranty_option_id');
            $table->dropColumn('warranty_period');
        });
    }
};
