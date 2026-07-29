<?php

namespace Tests\Feature;

use App\Models\FashionCategory;
use App\Models\FashionProduct;
use App\Models\HomeNeedCategory;
use App\Models\HomeNeedProduct;
use App\Models\MotorcycleProduct;
use App\Models\MotorcycleProductCategory;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_includes_all_public_module_product_pages(): void
    {
        $techCategory = Category::query()->create([
            'name' => 'Smart Devices',
            'status' => 'active',
        ]);

        $techBrand = Brand::query()->create([
            'name' => 'Stellebird',
            'status' => 'active',
        ]);

        Product::query()->create([
            'category_id' => $techCategory->id,
            'brand_id' => $techBrand->id,
            'sku' => 'STELLEBIRD-X1',
            'slug' => 'stellebird-helmet-x1',
            'model' => 'Stellebird Helmet X1',
            'device_status' => 'brandnew',
            'price_lkr' => 12500,
            'stock_count' => 6,
            'in_stock' => true,
            'status' => 'active',
        ]);

        $motorcycleCategory = MotorcycleProductCategory::query()->firstOrCreate(['name' => 'Helmets']);
        MotorcycleProduct::query()->create([
            'name' => 'Deze Full Face Helmet',
            'slug' => 'deze-full-face-helmet',
            'product_type' => 'helmet',
            'category_id' => $motorcycleCategory->id,
            'regular_price' => 18500,
            'stock_quantity' => 4,
            'stock_status' => 'in_stock',
            'status' => 'active',
        ]);

        $fashionCategory = FashionCategory::query()->firstOrCreate(['name' => 'Sunglasses']);
        FashionProduct::query()->create([
            'name' => 'Deze Polarized Sunglasses',
            'slug' => 'deze-polarized-sunglasses',
            'category_id' => $fashionCategory->id,
            'price' => 5900,
            'stock_quantity' => 9,
            'stock_status' => 'in_stock',
            'status' => 'active',
        ]);

        $homeNeedCategory = HomeNeedCategory::query()->firstOrCreate(['name' => 'Storage']);
        HomeNeedProduct::query()->create([
            'name' => 'Deze Storage Rack',
            'slug' => 'deze-storage-rack',
            'category_id' => $homeNeedCategory->id,
            'price' => 4500,
            'stock_quantity' => 7,
            'stock_status' => 'in_stock',
            'status' => 'active',
        ]);

        $response = $this->get('/sitemap.xml');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('/tech-products/stellebird-helmet-x1', false)
            ->assertSee('/motorcycle-products/deze-full-face-helmet', false)
            ->assertSee('/fashion/deze-polarized-sunglasses', false)
            ->assertSee('/home-needs/deze-storage-rack', false)
            ->assertSee('/motorcycle-products?type=helmet', false)
            ->assertSee('/fashion?category=Sunglasses', false)
            ->assertSee('/home-needs?category=Storage', false);
    }
}
