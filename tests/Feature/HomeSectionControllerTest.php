<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomeSectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_sections_still_load_when_module_flag_columns_are_missing(): void
    {
        foreach (['motorcycle_products', 'fashion_products', 'home_need_products'] as $tableName) {
            foreach (['today_best_deals', 'best_seller'] as $column) {
                if (!Schema::hasColumn($tableName, $column)) {
                    continue;
                }

                Schema::table($tableName, function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        $this->getJson('/home/sections/today-deals')
            ->assertOk()
            ->assertJsonStructure(['products', 'show_all_url']);

        $this->getJson('/home/sections/best-sellers')
            ->assertOk()
            ->assertJsonStructure(['products', 'show_all_url']);
    }

    public function test_marketing_sections_skip_a_source_when_its_query_fails(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        $this->getJson('/home/sections/today-deals')
            ->assertOk()
            ->assertJsonStructure(['products', 'show_all_url']);
    }

    public function test_marketing_sections_return_products_using_native_module_flag_columns(): void
    {
        $cosmeticId = $this->createCosmeticProduct([
            'hot_deals' => true,
            'best_selling' => true,
        ]);

        $homeNeedId = $this->createHomeNeedProduct([
            'today_best_deals' => true,
            'best_seller' => true,
        ]);

        $this->getJson('/home/sections/today-deals')
            ->assertOk()
            ->assertJsonFragment(['id' => 'cosmetic-' . $cosmeticId])
            ->assertJsonFragment(['id' => 'home-need-' . $homeNeedId]);

        $this->getJson('/home/sections/best-sellers')
            ->assertOk()
            ->assertJsonFragment(['id' => 'cosmetic-' . $cosmeticId])
            ->assertJsonFragment(['id' => 'home-need-' . $homeNeedId]);
    }

    public function test_marketing_sections_return_products_using_existing_alias_flag_columns(): void
    {
        Schema::table('cosmetic_products', function (Blueprint $table) {
            $table->boolean('today_best_deals')->default(false);
            $table->boolean('best_seller')->default(false);
        });

        Schema::table('home_need_products', function (Blueprint $table) {
            $table->boolean('hot_deals')->default(false);
            $table->boolean('best_selling')->default(false);
        });

        $cosmeticId = $this->createCosmeticProduct([
            'name' => 'Alias Cosmetic',
            'slug' => 'alias-cosmetic',
            'hot_deals' => false,
            'best_selling' => false,
        ]);

        DB::table('cosmetic_products')
            ->where('id', $cosmeticId)
            ->update([
                'today_best_deals' => true,
                'best_seller' => true,
            ]);

        $homeNeedId = $this->createHomeNeedProduct([
            'name' => 'Alias Home Need',
            'slug' => 'alias-home-need',
            'today_best_deals' => false,
            'best_seller' => false,
        ]);

        DB::table('home_need_products')
            ->where('id', $homeNeedId)
            ->update([
                'hot_deals' => true,
                'best_selling' => true,
            ]);

        $this->getJson('/home/sections/today-deals')
            ->assertOk()
            ->assertJsonFragment(['id' => 'cosmetic-' . $cosmeticId])
            ->assertJsonFragment(['id' => 'home-need-' . $homeNeedId]);

        $this->getJson('/home/sections/best-sellers')
            ->assertOk()
            ->assertJsonFragment(['id' => 'cosmetic-' . $cosmeticId])
            ->assertJsonFragment(['id' => 'home-need-' . $homeNeedId]);
    }

    private function createCosmeticProduct(array $overrides = []): int
    {
        $brandId = DB::table('cosmetic_brands')->insertGetId([
            'name' => 'Test Cosmetic Brand',
            'slug' => 'test-cosmetic-brand',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryId = DB::table('cosmetic_categories')->insertGetId([
            'name' => 'Test Cosmetic Category',
            'slug' => 'test-cosmetic-category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $typeId = DB::table('cosmetic_product_types')->insertGetId([
            'cosmetic_category_id' => $categoryId,
            'name' => 'Test Cosmetic Type',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $countryId = DB::table('cosmetic_countries_of_origin')->insertGetId([
            'name' => 'Test Country',
            'code' => 'TC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('cosmetic_products')->insertGetId(array_merge([
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'product_type_id' => $typeId,
            'country_of_origin_id' => $countryId,
            'name' => 'Native Cosmetic',
            'slug' => 'native-cosmetic',
            'price' => 1200,
            'stock' => 5,
            'is_featured' => false,
            'hot_deals' => false,
            'best_selling' => false,
            'status' => 'active',
            'main_image_path' => 'cosmetic-products/test.webp',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    private function createHomeNeedProduct(array $overrides = []): int
    {
        $categoryId = DB::table('home_need_categories')->insertGetId([
            'name' => 'Test Home Category',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('home_need_products')->insertGetId(array_merge([
            'name' => 'Native Home Need',
            'slug' => 'native-home-need',
            'category_id' => $categoryId,
            'price' => 2200,
            'stock_quantity' => 3,
            'stock_status' => 'in_stock',
            'status' => 'active',
            'featured' => false,
            'today_best_deals' => false,
            'best_seller' => false,
            'main_image_path' => 'home-needs/test.webp',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
