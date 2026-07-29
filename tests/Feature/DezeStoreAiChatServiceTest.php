<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\HomeNeedBrand;
use App\Models\HomeNeedCategory;
use App\Models\HomeNeedProduct;
use App\Models\Product;
use App\Services\DezeStoreAiChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DezeStoreAiChatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.ai.provider', 'openai');
        config()->set('services.openai.api_key', null);
        config()->set('services.gemini.api_key', null);
    }

    public function test_non_product_questions_do_not_attach_catalog_cards(): void
    {
        $this->createTechProduct('Memo CX07', 'MEMO-CX07');

        $answer = app(DezeStoreAiChatService::class)->answer('Can you explain delivery details?');

        $this->assertSame([], $answer['products']);
        $this->assertStringContainsString('Delivery details are confirmed', $answer['message']);

        $otherAnswer = app(DezeStoreAiChatService::class)->answer('Can you give another information?');

        $this->assertSame([], $otherAnswer['products']);
    }

    public function test_catalog_questions_attach_product_cards(): void
    {
        $this->createTechProduct('Memo CX07', 'MEMO-CX07');
        $this->createTechProduct('Memo CX08', 'MEMO-CX08');

        $answer = app(DezeStoreAiChatService::class)->answer('What products are in stock?');

        $this->assertCount(2, $answer['products']);
        $this->assertSame(['Memo CX07', 'Memo CX08'], array_column($answer['products'], 'name'));
    }

    public function test_specific_product_questions_return_only_the_best_match(): void
    {
        $this->createTechProduct('Memo CX07', 'MEMO-CX07');
        $this->createTechProduct('Memo CX08', 'MEMO-CX08');

        $answer = app(DezeStoreAiChatService::class)->answer('What is the price of Memo CX07?');

        $this->assertCount(1, $answer['products']);
        $this->assertSame('Memo CX07', $answer['products'][0]['name']);
        $this->assertStringContainsString('Memo CX07', $answer['message']);
    }

    public function test_chatbot_searches_home_needs_products(): void
    {
        $category = HomeNeedCategory::query()->create(['name' => 'Kitchen']);
        $brand = HomeNeedBrand::query()->create(['name' => 'Deze Home']);

        HomeNeedProduct::query()->create([
            'name' => 'Deze Storage Rack',
            'slug' => 'deze-storage-rack',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'sku' => 'HOME-RACK-01',
            'price' => 4500,
            'stock_quantity' => 7,
            'stock_status' => 'in_stock',
            'status' => 'active',
            'short_description' => 'Kitchen storage rack',
        ]);

        $answer = app(DezeStoreAiChatService::class)->answer('Do you have Deze Storage Rack?');

        $this->assertCount(1, $answer['products']);
        $this->assertSame('Deze Storage Rack', $answer['products'][0]['name']);
        $this->assertStringContainsString('Deze Storage Rack', $answer['message']);
    }

    private function createTechProduct(string $model, string $sku): Product
    {
        $brand = Brand::query()->firstOrCreate(['name' => 'Memo']);
        $category = Category::query()->firstOrCreate(['name' => 'Phone Cooler']);

        return Product::query()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'sku' => $sku,
            'model' => $model,
            'device_status' => 'brandnew',
            'price_lkr' => 12500,
            'in_stock' => true,
            'stock_count' => 5,
            'status' => 'active',
            'short_description' => $model . ' cooling accessory',
        ]);
    }
}
