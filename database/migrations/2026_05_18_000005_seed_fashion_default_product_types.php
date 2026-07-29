<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'Jewelry' => ['Necklaces'],
            'Bags' => ['Bags'],
            'Fashion Wear' => ['Fashion Wear'],
            'Sunglasses' => ['Sunglasses'],
        ];

        foreach ($defaults as $categoryName => $typeNames) {
            $categoryId = DB::table('fashion_categories')
                ->where('name', $categoryName)
                ->value('id');

            if (!$categoryId) {
                $categoryId = DB::table('fashion_categories')->insertGetId([
                    'name' => $categoryName,
                    'description' => null,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($typeNames as $typeName) {
                $typeExists = DB::table('fashion_product_types')
                    ->where('category_id', $categoryId)
                    ->where('name', $typeName)
                    ->exists();

                if (!$typeExists) {
                    DB::table('fashion_product_types')->insert([
                        'category_id' => $categoryId,
                        'name' => $typeName,
                        'description' => null,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $typeNames = ['Necklaces', 'Bags', 'Fashion Wear', 'Sunglasses'];
        $categoryNames = ['Jewelry', 'Bags', 'Fashion Wear', 'Sunglasses'];

        DB::table('fashion_product_types')
            ->whereIn('name', $typeNames)
            ->delete();

        DB::table('fashion_categories')
            ->whereIn('name', $categoryNames)
            ->delete();
    }
};
