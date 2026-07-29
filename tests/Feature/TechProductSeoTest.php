<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechProductSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_tech_product_show_redirects_id_url_to_slug_url(): void
    {
        $product = $this->createTechProduct([
            'slug' => 'stellebird-helmet-x1',
        ]);

        $response = $this->get('/tech-products/' . $product->id);

        $response->assertRedirect('/tech-products/' . $product->slug);
        $this->assertSame(301, $response->getStatusCode());
    }

    public function test_tech_product_data_can_be_loaded_by_slug(): void
    {
        $product = $this->createTechProduct([
            'slug' => 'stellebird-helmet-x1',
        ]);

        $this->getJson('/tech-products/' . $product->slug . '/data')
            ->assertOk()
            ->assertJsonPath('product.slug', $product->slug)
            ->assertJsonPath('product.name', $product->model);
    }

    private function createTechProduct(array $overrides = []): Product
    {
        $category = Category::query()->create([
            'name' => 'Helmets',
            'status' => 'active',
        ]);

        $brand = Brand::query()->create([
            'name' => 'Stellebird',
            'status' => 'active',
        ]);

        return Product::query()->create(array_merge([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'sku' => 'STELLEBIRD-X1',
            'slug' => 'stellebird-helmet',
            'model' => 'Stellebird Helmet X1',
            'device_status' => 'brandnew',
            'price_lkr' => 12500,
            'in_stock' => true,
            'stock_count' => 6,
            'status' => 'active',
        ], $overrides));
    }
}
