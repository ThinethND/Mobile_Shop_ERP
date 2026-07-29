<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('sku');
        });

        $brandNames = DB::table('brands')->pluck('name', 'id');
        $usedSlugs = [];

        DB::table('products')
            ->select(['id', 'brand_id', 'model'])
            ->orderBy('id')
            ->chunkById(200, function ($products) use ($brandNames, &$usedSlugs) {
                foreach ($products as $product) {
                    $source = trim(implode(' ', array_filter([
                        $brandNames[$product->brand_id] ?? null,
                        $product->model,
                    ])));

                    $baseSlug = Str::slug($source);
                    $baseSlug = $baseSlug !== '' ? $baseSlug : 'product-' . $product->id;
                    $candidate = $baseSlug;
                    $suffix = 2;

                    while (isset($usedSlugs[$candidate])) {
                        $candidate = $baseSlug . '-' . $suffix;
                        $suffix++;
                    }

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['slug' => $candidate]);

                    $usedSlugs[$candidate] = true;
                }
            });

        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
