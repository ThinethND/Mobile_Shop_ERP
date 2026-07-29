<?php

namespace Tests\Feature;

use App\Models\MotorcycleProduct;
use App\Models\MotorcycleProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleProductSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_motorcycle_product_slug_page_builds_seo_without_error(): void
    {
        $category = MotorcycleProductCategory::query()->create([
            'name' => 'Helmets',
            'status' => 'active',
        ]);

        MotorcycleProduct::query()->create([
            'name' => 'Full Facee',
            'slug' => 'full-facee',
            'product_type' => 'helmet',
            'category_id' => $category->id,
            'regular_price' => 18500,
            'stock_quantity' => 4,
            'stock_status' => 'in_stock',
            'status' => 'active',
        ]);

        $this->get('/motorcycle-products/full-facee')
            ->assertOk()
            ->assertSee('/motorcycle-products/full-facee', false);
    }
}
