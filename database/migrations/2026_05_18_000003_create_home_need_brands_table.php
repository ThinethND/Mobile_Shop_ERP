<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_need_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::table('home_need_products', function (Blueprint $table) {
            $table->foreignId('brand_id')
                ->nullable()
                ->after('category_id')
                ->constrained('home_need_brands')
                ->nullOnDelete();
        });

        $brandNames = DB::table('home_need_products')
            ->whereNotNull('brand_name')
            ->where('brand_name', '!=', '')
            ->select('brand_name')
            ->distinct()
            ->pluck('brand_name');

        foreach ($brandNames as $brandName) {
            $brandId = DB::table('home_need_brands')->insertGetId([
                'name' => $brandName,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('home_need_products')
                ->where('brand_name', $brandName)
                ->update(['brand_id' => $brandId]);
        }
    }

    public function down(): void
    {
        Schema::table('home_need_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
        });

        Schema::dropIfExists('home_need_brands');
    }
};
