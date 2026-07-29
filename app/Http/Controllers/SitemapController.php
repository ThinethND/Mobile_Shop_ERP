<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CosmeticBrand;
use App\Models\CosmeticCategory;
use App\Models\CosmeticProduct;
use App\Models\FashionBrand;
use App\Models\FashionCategory;
use App\Models\FashionProduct;
use App\Models\FashionProductType;
use App\Models\HomeBanner;
use App\Models\HomeNeedBrand;
use App\Models\HomeNeedCategory;
use App\Models\HomeNeedProduct;
use App\Models\MotorcycleHelmetBrand;
use App\Models\MotorcycleProduct;
use App\Models\MotorcycleProductCategory;
use App\Models\Product;
use App\Models\ShoeCategory;
use App\Models\ShoeProduct;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $entries = collect();

        $this->addEntry($entries, $this->routeUrl('frontend.root'), $this->latestTimestamp([
            Product::class,
            ShoeProduct::class,
            CosmeticProduct::class,
            MotorcycleProduct::class,
            FashionProduct::class,
            HomeNeedProduct::class,
            HomeBanner::class,
        ]), 'daily', '1.0');

        $this->addEntry($entries, $this->routeUrl('frontend.contact-us.index'), now(), 'monthly', '0.6');
        $this->addEntry($entries, $this->routeUrl('frontend.tech-products.index'), $this->latestTimestamp([
            Product::class,
            Category::class,
            Brand::class,
        ]), 'weekly', '0.8');
        $this->addEntry($entries, $this->routeUrl('frontend.shoe-products.index'), $this->latestTimestamp([
            ShoeProduct::class,
            ShoeCategory::class,
        ]), 'weekly', '0.8');
        $this->addEntry($entries, $this->routeUrl('frontend.cosmetic-products.index'), $this->latestTimestamp([
            CosmeticProduct::class,
            CosmeticCategory::class,
            CosmeticBrand::class,
        ]), 'weekly', '0.8');
        $this->addEntry($entries, $this->routeUrl('frontend.motorcycle-products.index'), $this->latestTimestamp([
            MotorcycleProduct::class,
            MotorcycleProductCategory::class,
            MotorcycleHelmetBrand::class,
        ]), 'weekly', '0.8');
        $this->addEntry($entries, $this->routeUrl('frontend.fashion.index'), $this->latestTimestamp([
            FashionProduct::class,
            FashionCategory::class,
            FashionBrand::class,
            FashionProductType::class,
        ]), 'weekly', '0.8');
        $this->addEntry($entries, $this->routeUrl('frontend.home-needs.index'), $this->latestTimestamp([
            HomeNeedProduct::class,
            HomeNeedCategory::class,
            HomeNeedBrand::class,
        ]), 'weekly', '0.8');

        $this->addTechCategoryEntries($entries);
        $this->addTechBrandEntries($entries);
        $this->addShoeCategoryEntries($entries);
        $this->addCosmeticCategoryEntries($entries);
        $this->addCosmeticBrandEntries($entries);
        $this->addModuleFilterEntries($entries, $this->moduleSitemapConfig('motorcycle'));
        $this->addModuleFilterEntries($entries, $this->moduleSitemapConfig('fashion'));
        $this->addModuleFilterEntries($entries, $this->moduleSitemapConfig('home-needs'));
        $this->addTechProductEntries($entries);
        $this->addShoeProductEntries($entries);
        $this->addCosmeticProductEntries($entries);
        $this->addModuleProductEntries($entries, $this->moduleSitemapConfig('motorcycle'));
        $this->addModuleProductEntries($entries, $this->moduleSitemapConfig('fashion'));
        $this->addModuleProductEntries($entries, $this->moduleSitemapConfig('home-needs'));

        $xml = view('sitemap.index', [
            'entries' => $entries
                ->sortBy('loc')
                ->values(),
        ])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    private function addTechCategoryEntries(Collection $entries): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->each(function (Category $category) use ($entries) {
                $this->addEntry(
                    $entries,
                    $this->routeUrl('frontend.tech-products.index', ['category' => $category->name]),
                    $category->updated_at ?: now(),
                    'weekly',
                    '0.8'
                );
            });
    }

    private function addTechBrandEntries(Collection $entries): void
    {
        if (! Schema::hasTable('categories') || ! Schema::hasTable('brands')) {
            return;
        }

        Category::query()
            ->where('status', 'active')
            ->with([
                'brands' => fn ($query) => $query
                    ->where('brands.status', 'active')
                    ->orderBy('brands.name'),
            ])
            ->get()
            ->each(function (Category $category) use ($entries) {
                foreach ($category->brands as $brand) {
                    $this->addEntry(
                        $entries,
                        $this->routeUrl('frontend.tech-products.index', [
                            'category' => $category->name,
                            'brand' => $brand->name,
                        ]),
                        $brand->updated_at ?: $category->updated_at ?: now(),
                        'weekly',
                        '0.6'
                    );
                }
            });
    }

    private function addShoeCategoryEntries(Collection $entries): void
    {
        if (! Schema::hasTable('shoes_categories')) {
            return;
        }

        ShoeCategory::query()
            ->where('status', 'active')
            ->with([
                'subcategories' => fn ($query) => $query
                    ->where('status', 'active')
                    ->orderBy('name'),
            ])
            ->orderBy('name')
            ->get()
            ->each(function (ShoeCategory $category) use ($entries) {
                $this->addEntry(
                    $entries,
                    $this->routeUrl('frontend.shoe-products.index', ['shoe_category' => $category->name]),
                    $category->updated_at ?: now(),
                    'weekly',
                    '0.8'
                );

                foreach ($category->subcategories as $subcategory) {
                    $this->addEntry(
                        $entries,
                        $this->routeUrl('frontend.shoe-products.index', [
                            'shoe_category' => $category->name,
                            'shoe_subcategory' => $subcategory->name,
                        ]),
                        $subcategory->updated_at ?: $category->updated_at ?: now(),
                        'weekly',
                        '0.7'
                    );
                }
            });
    }

    private function addCosmeticCategoryEntries(Collection $entries): void
    {
        if (! Schema::hasTable('cosmetic_categories')) {
            return;
        }

        CosmeticCategory::query()
            ->orderBy('name')
            ->get()
            ->each(function (CosmeticCategory $category) use ($entries) {
                $this->addEntry(
                    $entries,
                    $this->routeUrl('frontend.cosmetic-products.index', ['cosmetic_category' => $category->name]),
                    $category->updated_at ?: now(),
                    'weekly',
                    '0.8'
                );
            });
    }

    private function addCosmeticBrandEntries(Collection $entries): void
    {
        if (
            ! Schema::hasTable('cosmetic_products')
            || ! Schema::hasTable('cosmetic_categories')
            || ! Schema::hasTable('cosmetic_brands')
        ) {
            return;
        }

        CosmeticCategory::query()
            ->with([
                'products' => fn ($query) => $query
                    ->where('status', 'active')
                    ->with('brand'),
            ])
            ->get()
            ->each(function (CosmeticCategory $category) use ($entries) {
                $category->products
                    ->filter(fn (CosmeticProduct $product) => $product->brand && $product->brand->status === 'active')
                    ->groupBy(fn (CosmeticProduct $product) => $product->brand?->id)
                    ->each(function ($products) use ($entries, $category) {
                        /** @var CosmeticProduct $firstProduct */
                        $firstProduct = $products->first();
                        $brand = $firstProduct?->brand;

                        if (! $brand) {
                            return;
                        }

                        $this->addEntry(
                            $entries,
                            $this->routeUrl('frontend.cosmetic-products.index', [
                                'cosmetic_category' => $category->name,
                                'cosmetic_brand' => $brand->name,
                            ]),
                            $brand->updated_at ?: $category->updated_at ?: now(),
                            'weekly',
                            '0.6'
                        );
                    });
            });
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function addModuleFilterEntries(Collection $entries, array $config): void
    {
        $productModel = $config['product_model'];
        $categoryModel = $config['category_model'];
        $brandModel = $config['brand_model'];
        $typeModel = $config['type_model'];

        if (Schema::hasTable((new $categoryModel())->getTable())) {
            $categoryModel::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
                ->each(function ($category) use ($entries, $config) {
                    $this->addEntry(
                        $entries,
                        $this->routeUrl($config['index_route'], ['category' => $category->name]),
                        $category->updated_at ?: now(),
                        'weekly',
                        '0.8'
                    );
                });
        }

        if (Schema::hasTable((new $brandModel())->getTable())) {
            $brandModel::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
                ->each(function ($brand) use ($entries, $config) {
                    $this->addEntry(
                        $entries,
                        $this->routeUrl($config['index_route'], ['brand' => $brand->name]),
                        $brand->updated_at ?: now(),
                        'weekly',
                        '0.6'
                    );
                });
        }

        $productTable = (new $productModel())->getTable();

        if (
            Schema::hasTable($productTable)
            && Schema::hasColumn($productTable, 'category_id')
            && Schema::hasColumn($productTable, $config['brand_foreign_key'])
        ) {
            $productModel::query()
                ->where('status', 'active')
                ->whereNotNull('category_id')
                ->whereNotNull($config['brand_foreign_key'])
                ->with([$config['category_relation'], $config['brand_relation']])
                ->orderBy('id')
                ->get()
                ->each(function ($product) use ($entries, $config) {
                    $category = $product->{$config['category_relation']};
                    $brand = $product->{$config['brand_relation']};

                    if (! $category || ! $brand || ($category->status ?? 'active') !== 'active' || ($brand->status ?? 'active') !== 'active') {
                        return;
                    }

                    $this->addEntry(
                        $entries,
                        $this->routeUrl($config['index_route'], [
                            'category' => $category->name,
                            'brand' => $brand->name,
                        ]),
                        $product->updated_at ?: $brand->updated_at ?: $category->updated_at ?: now(),
                        'weekly',
                        '0.6'
                    );
                });
        }

        if ($typeModel && Schema::hasTable((new $typeModel())->getTable())) {
            $typeModel::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
                ->each(function ($type) use ($entries, $config) {
                    $this->addEntry(
                        $entries,
                        $this->routeUrl($config['index_route'], ['type' => $type->name]),
                        $type->updated_at ?: now(),
                        'weekly',
                        '0.6'
                    );
                });
        }

        foreach ($config['static_types'] as $type) {
            $this->addEntry(
                $entries,
                $this->routeUrl($config['index_route'], ['type' => $type]),
                now(),
                'weekly',
                '0.6'
            );
        }
    }

    private function addTechProductEntries(Collection $entries): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Product::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get()
            ->each(function (Product $product) use ($entries) {
                $this->addEntry(
                    $entries,
                    $this->routeUrl('frontend.tech-products.show', ['product' => $product->routeIdentifier()]),
                    $product->updated_at ?: now(),
                    'weekly',
                    '0.7'
                );
            });
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function addModuleProductEntries(Collection $entries, array $config): void
    {
        $modelClass = $config['product_model'];
        $model = new $modelClass();

        if (! Schema::hasTable($model->getTable())) {
            return;
        }

        $modelClass::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get()
            ->each(function ($product) use ($entries, $config) {
                $this->addEntry(
                    $entries,
                    $this->routeUrl($config['show_route'], [
                        'product' => filled($product->slug) ? $product->slug : $product->id,
                    ]),
                    $product->updated_at ?: now(),
                    'weekly',
                    '0.7'
                );
            });
    }

    private function addShoeProductEntries(Collection $entries): void
    {
        if (! Schema::hasTable('shoe_products')) {
            return;
        }

        ShoeProduct::query()
            ->where('status', 'published')
            ->orderBy('id')
            ->get()
            ->each(function (ShoeProduct $product) use ($entries) {
                $this->addEntry(
                    $entries,
                    $this->routeUrl('frontend.shoe-products.show', [
                        'product' => filled($product->slug) ? $product->slug : $product->id,
                    ]),
                    $product->updated_at ?: now(),
                    'weekly',
                    '0.7'
                );
            });
    }

    private function addCosmeticProductEntries(Collection $entries): void
    {
        if (! Schema::hasTable('cosmetic_products')) {
            return;
        }

        CosmeticProduct::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get()
            ->each(function (CosmeticProduct $product) use ($entries) {
                $this->addEntry(
                    $entries,
                    $this->routeUrl('frontend.cosmetic-products.show', [
                        'product' => filled($product->slug) ? $product->slug : $product->id,
                    ]),
                    $product->updated_at ?: now(),
                    'weekly',
                    '0.7'
                );
            });
    }

    private function routeUrl(string $name, array $params = []): string
    {
        $baseUrl = rtrim((string) config('app.url', url('/')), '/');
        $path = route($name, $params, false);

        return $baseUrl . ($path === '/' ? '/' : $path);
    }

    private function addEntry(Collection $entries, string $loc, $lastmod, string $changefreq, string $priority): void
    {
        if ($entries->contains(fn (array $entry) => $entry['loc'] === $loc)) {
            return;
        }

        $entries->push([
            'loc' => $loc,
            'lastmod' => $lastmod ? $lastmod->toAtomString() : now()->toAtomString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function moduleSitemapConfig(string $section): array
    {
        return match ($section) {
            'motorcycle' => [
                'product_model' => MotorcycleProduct::class,
                'category_model' => MotorcycleProductCategory::class,
                'brand_model' => MotorcycleHelmetBrand::class,
                'category_relation' => 'category',
                'brand_relation' => 'helmetBrand',
                'brand_foreign_key' => 'helmet_brand_id',
                'type_model' => null,
                'static_types' => MotorcycleProduct::TYPES,
                'index_route' => 'frontend.motorcycle-products.index',
                'show_route' => 'frontend.motorcycle-products.show',
            ],
            'fashion' => [
                'product_model' => FashionProduct::class,
                'category_model' => FashionCategory::class,
                'brand_model' => FashionBrand::class,
                'category_relation' => 'category',
                'brand_relation' => 'brand',
                'brand_foreign_key' => 'brand_id',
                'type_model' => FashionProductType::class,
                'static_types' => [],
                'index_route' => 'frontend.fashion.index',
                'show_route' => 'frontend.fashion.show',
            ],
            'home-needs' => [
                'product_model' => HomeNeedProduct::class,
                'category_model' => HomeNeedCategory::class,
                'brand_model' => HomeNeedBrand::class,
                'category_relation' => 'category',
                'brand_relation' => 'brand',
                'brand_foreign_key' => 'brand_id',
                'type_model' => null,
                'static_types' => [],
                'index_route' => 'frontend.home-needs.index',
                'show_route' => 'frontend.home-needs.show',
            ],
            default => throw new \InvalidArgumentException('Unsupported sitemap section.'),
        };
    }

    /**
     * @param  array<int, class-string>  $models
     */
    private function latestTimestamp(array $models)
    {
        return collect($models)
            ->filter(fn (string $modelClass) => class_exists($modelClass))
            ->map(function (string $modelClass) {
                $model = new $modelClass();

                if (! Schema::hasTable($model->getTable())) {
                    return null;
                }

                if (! Schema::hasColumn($model->getTable(), 'updated_at')) {
                    return now();
                }

                return $modelClass::query()->latest('updated_at')->value('updated_at');
            })
            ->filter()
            ->map(fn ($value) => Carbon::parse($value))
            ->sortDesc()
            ->first() ?: now();
    }
}
