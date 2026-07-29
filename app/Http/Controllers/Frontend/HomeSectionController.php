<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ColorOption;
use App\Models\CosmeticCategory;
use App\Models\CosmeticProduct;
use App\Models\FashionCategory;
use App\Models\FashionProduct;
use App\Models\HomeNeedCategory;
use App\Models\HomeNeedProduct;
use App\Models\KokoPaySetting;
use App\Models\MotorcycleProduct;
use App\Models\MotorcycleProductCategory;
use App\Models\Product;
use App\Support\KokoPay;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HomeSectionController extends Controller
{
    protected array $schemaColumnCache = [];

    public function todayDeals(): JsonResponse
    {
        return $this->marketingResponse('deals', 6);
    }

    public function bestSellers(): JsonResponse
    {
        return $this->marketingResponse('best_seller', 6);
    }

    public function categorySection(Request $request, string $section): JsonResponse
    {
        $categoryId = $this->categoryIdFromRequest($request);

        return match ($section) {
            'motorcycle' => $this->categoryResponse(
                $this->motorcycleCategories(),
                $this->motorcycleProducts(4, $categoryId),
                route('frontend.motorcycle-products.index'),
                $categoryId,
                'frontend.motorcycle-products.index'
            ),
            'electronics' => $this->categoryResponse(
                $this->techCategories(),
                $this->techProducts(4, $categoryId),
                route('frontend.tech-products.index'),
                $categoryId,
                'frontend.tech-products.index'
            ),
            'cosmetics' => $this->categoryResponse(
                $this->cosmeticCategories(),
                $this->cosmeticProducts(4, $categoryId),
                route('frontend.cosmetic-products.index'),
                $categoryId,
                'frontend.cosmetic-products.index',
                'cosmetic_category'
            ),
            'fashion' => $this->categoryResponse(
                $this->fashionCategories(),
                $this->fashionProducts(4, $categoryId),
                route('frontend.fashion.index'),
                $categoryId,
                'frontend.fashion.index'
            ),
            'home-needs' => $this->categoryResponse(
                $this->homeNeedCategories(),
                $this->homeNeedProducts(4, $categoryId),
                route('frontend.home-needs.index'),
                $categoryId,
                'frontend.home-needs.index'
            ),
            default => response()->json(['message' => 'Unknown home section.'], 404),
        };
    }

    protected function productsResponse(Collection $products, string $showAllUrl, int $limit): JsonResponse
    {
        return response()
            ->json([
                'products' => $this->publicProducts($products)->take($limit)->values(),
                'show_all_url' => $showAllUrl,
            ])
            ->header('Cache-Control', 'private, max-age=90')
            ->header('X-Home-Section-Version', '2026-05-22-flag-aliases');
    }

    protected function categoryResponse(
        Collection $categories,
        Collection $products,
        string $fallbackShowAllUrl,
        ?int $categoryId,
        string $routeName,
        string $queryKey = 'category'
    ): JsonResponse {
        $activeCategory = $categoryId
            ? $categories->firstWhere('id', $categoryId)
            : null;

        $showAllUrl = $activeCategory
            ? route($routeName, [$queryKey => $activeCategory['name']])
            : $fallbackShowAllUrl;

        return response()
            ->json([
                'categories' => $categories->values(),
                'products' => $this->publicProducts($products)->take(4)->values(),
                'active_category' => $activeCategory,
                'show_all_url' => $showAllUrl,
            ])
            ->header('Cache-Control', 'private, max-age=90');
    }

    protected function categoryIdFromRequest(Request $request): ?int
    {
        $category = $request->query('category');

        if ($category === null || $category === '') {
            return null;
        }

        return is_numeric($category) ? (int) $category : null;
    }

    protected function marketingResponse(string $flag, int $limit): JsonResponse
    {
        try {
            return $this->productsResponse(
                $this->mixedProducts($flag, $limit),
                route('frontend.root'),
                $limit
            );
        } catch (Throwable $exception) {
            Log::error('Home marketing section failed.', [
                'flag' => $flag,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return response()
                ->json([
                    'products' => [],
                    'show_all_url' => url('/home'),
                ])
                ->header('Cache-Control', 'private, max-age=30')
                ->header('X-Home-Section-Version', '2026-05-22-flag-aliases');
        }
    }

    protected function mixedProducts(string $flag, int $limit): Collection
    {
        return $this->marketingProductsFrom('electronics', $flag, fn () => $this->techProducts($limit, null, $flag))
            ->merge($this->marketingProductsFrom('motorcycle', $flag, fn () => $this->motorcycleProducts($limit, null, $flag)))
            ->merge($this->marketingProductsFrom('cosmetics', $flag, fn () => $this->cosmeticProducts($limit, null, $flag)))
            ->merge($this->marketingProductsFrom('fashion', $flag, fn () => $this->fashionProducts($limit, null, $flag)))
            ->merge($this->marketingProductsFrom('home-needs', $flag, fn () => $this->homeNeedProducts($limit, null, $flag)))
            ->sortByDesc('sort_key')
            ->take($limit)
            ->values();
    }

    protected function marketingProductsFrom(string $source, string $flag, callable $products): Collection
    {
        try {
            $result = $products();

            if ($result instanceof EloquentCollection) {
                return $result->toBase()->values();
            }

            if ($result instanceof Collection) {
                return $result->values();
            }

            return collect($result)->values();
        } catch (Throwable $exception) {
            Log::warning('Home marketing product source failed.', [
                'source' => $source,
                'flag' => $flag,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    protected function techCategories(): Collection
    {
        return Category::query()
            ->select(['id', 'name'])
            ->selectSub(
                Product::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('products.category_id', 'categories.id')
                    ->where('status', 'active'),
                'products_count'
            )
            ->where('status', 'active')
            ->oldest('id')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'count' => (int) ($category->products_count ?? 0),
            ]);
    }

    protected function motorcycleCategories(): Collection
    {
        return MotorcycleProductCategory::query()
            ->select(['id', 'name'])
            ->withCount([
                'products as products_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->where('status', 'active')
            ->oldest('id')
            ->get()
            ->map(fn (MotorcycleProductCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'count' => (int) ($category->products_count ?? 0),
            ]);
    }

    protected function cosmeticCategories(): Collection
    {
        return CosmeticCategory::query()
            ->select(['id', 'name'])
            ->withCount([
                'products as products_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (CosmeticCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'count' => (int) ($category->products_count ?? 0),
            ]);
    }

    protected function fashionCategories(): Collection
    {
        return FashionCategory::query()
            ->select(['id', 'name'])
            ->withCount([
                'products as products_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (FashionCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'count' => (int) ($category->products_count ?? 0),
            ]);
    }

    protected function homeNeedCategories(): Collection
    {
        return HomeNeedCategory::query()
            ->select(['id', 'name'])
            ->withCount([
                'products as products_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (HomeNeedCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'count' => (int) ($category->products_count ?? 0),
            ]);
    }

    protected function techProducts(int $limit, ?int $categoryId = null, ?string $flag = null): Collection
    {
        $flagColumns = $this->marketingFlagColumns(
            $flag,
            'products',
            ['is_deal_of_the_day', 'today_best_deals', 'hot_deals'],
            ['is_best_seller', 'best_seller', 'best_selling']
        );

        if ($flag !== null && empty($flagColumns)) {
            return collect();
        }

        $products = Product::query()
            ->with(['category:id,name', 'brand:id,name'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when(!empty($flagColumns), fn ($query) => $this->whereAnyMarketingFlag($query, $flagColumns))
            ->latest('id')
            ->take($limit)
            ->get();

        $colorIds = $products
            ->flatMap(fn (Product $product) => collect($product->color_ids ?? [])->map(fn ($id) => (int) $id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $colorMap = ColorOption::query()
            ->select('id', 'name', 'color_code')
            ->whereIn('id', $colorIds)
            ->get()
            ->keyBy('id');

        return $products->map(fn (Product $product) => $this->techCard($product, $colorMap));
    }

    protected function motorcycleProducts(int $limit, ?int $categoryId = null, ?string $flag = null): Collection
    {
        $flagColumns = $this->marketingFlagColumns(
            $flag,
            'motorcycle_products',
            ['today_best_deals', 'is_deal_of_the_day', 'hot_deals'],
            ['best_seller', 'is_best_seller', 'best_selling']
        );

        if ($flag !== null && empty($flagColumns)) {
            return collect();
        }

        return MotorcycleProduct::query()
            ->with(['category:id,name', 'helmetBrand:id,name'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when(!empty($flagColumns), fn ($query) => $this->whereAnyMarketingFlag($query, $flagColumns))
            ->latest('id')
            ->take($limit)
            ->get()
            ->map(fn (MotorcycleProduct $product) => $this->motorcycleCard($product));
    }

    protected function cosmeticProducts(int $limit, ?int $categoryId = null, ?string $flag = null): Collection
    {
        $flagColumns = $this->marketingFlagColumns(
            $flag,
            'cosmetic_products',
            ['hot_deals', 'today_best_deals', 'is_deal_of_the_day'],
            ['best_selling', 'best_seller', 'is_best_seller']
        );

        if ($flag !== null && empty($flagColumns)) {
            return collect();
        }

        return CosmeticProduct::query()
            ->with(['category:id,name', 'brand:id,name'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when(!empty($flagColumns), fn ($query) => $this->whereAnyMarketingFlag($query, $flagColumns))
            ->latest('id')
            ->take($limit)
            ->get()
            ->map(fn (CosmeticProduct $product) => $this->cosmeticCard($product));
    }

    protected function fashionProducts(int $limit, ?int $categoryId = null, ?string $flag = null): Collection
    {
        $flagColumns = $this->marketingFlagColumns(
            $flag,
            'fashion_products',
            ['today_best_deals', 'is_deal_of_the_day', 'hot_deals'],
            ['best_seller', 'is_best_seller', 'best_selling']
        );

        if ($flag !== null && empty($flagColumns)) {
            return collect();
        }

        return FashionProduct::query()
            ->with(['category:id,name', 'brand:id,name'])
            ->where('status', 'active')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when(!empty($flagColumns), fn ($query) => $this->whereAnyMarketingFlag($query, $flagColumns))
            ->latest('id')
            ->take($limit)
            ->get()
            ->map(fn (FashionProduct $product) => $this->fashionCard($product));
    }

    protected function homeNeedProducts(int $limit, ?int $categoryId = null, ?string $flag = null): Collection
    {
        $flagColumns = $this->marketingFlagColumns(
            $flag,
            'home_need_products',
            ['today_best_deals', 'is_deal_of_the_day', 'hot_deals'],
            ['best_seller', 'is_best_seller', 'best_selling']
        );

        if ($flag !== null && empty($flagColumns)) {
            return collect();
        }

        return HomeNeedProduct::query()
            ->with(['category:id,name', 'brand:id,name'])
            ->where('status', 'active')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when(!empty($flagColumns), fn ($query) => $this->whereAnyMarketingFlag($query, $flagColumns))
            ->latest('id')
            ->take($limit)
            ->get()
            ->map(fn (HomeNeedProduct $product) => $this->homeNeedCard($product));
    }

    protected function techCard(Product $product, Collection $colorMap): array
    {
        $regularPrice = (float) ($product->price_lkr ?? 0);
        [$displayPrice, $discountLabel] = $this->discountedPrice(
            $regularPrice,
            $product->discount_type,
            $product->discount_value
        );

        $colors = collect($product->color_ids ?? [])
            ->map(function ($colorId) use ($colorMap) {
                $color = $colorMap->get((int) $colorId);

                if (!$color) {
                    return null;
                }

                return [
                    'id' => $color->id,
                    'name' => $color->name,
                    'color_code' => $color->color_code,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'id' => 'tech-' . $product->id,
            'name' => $product->model ?? '',
            'slug' => $product->slug,
            'category_name' => $product->category?->name,
            'brand_name' => $product->brand?->name,
            'thumbnail_url' => $product->main_image_url,
            'hover_image_url' => $product->hover_image_url,
            'regular_price' => $regularPrice,
            'display_price' => $displayPrice,
            'has_discount' => $displayPrice < $regularPrice,
            'discount_label' => $discountLabel,
            'is_sold_out' => !$product->in_stock || ($product->stock_count !== null && (int) $product->stock_count <= 0),
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'colors' => $colors,
            'url' => route('frontend.tech-products.show', ['product' => $product->routeIdentifier()]),
            'sort_key' => $product->id,
        ];
    }

    protected function motorcycleCard(MotorcycleProduct $product): array
    {
        $regularPrice = (float) ($product->regular_price ?? 0);
        $displayPrice = $product->sale_price !== null && (float) $product->sale_price > 0
            ? (float) $product->sale_price
            : $regularPrice;

        return [
            'id' => 'motorcycle-' . $product->id,
            'name' => $product->name,
            'category_name' => $product->category?->name,
            'brand_name' => $product->helmetBrand?->name,
            'thumbnail_url' => $product->main_image_url,
            'hover_image_url' => $product->hover_image_url,
            'regular_price' => $regularPrice,
            'display_price' => $displayPrice,
            'has_discount' => $displayPrice < $regularPrice,
            'discount_label' => $this->discountLabel($regularPrice, $displayPrice),
            'is_sold_out' => $product->stock_status === 'out_of_stock' || ((int) ($product->stock_quantity ?? 0) <= 0),
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'colors' => [],
            'url' => route('frontend.motorcycle-products.show', [
                'product' => filled($product->slug) ? $product->slug : $product->id,
            ]),
            'sort_key' => $product->id,
        ];
    }

    protected function cosmeticCard(CosmeticProduct $product): array
    {
        $regularPrice = (float) ($product->price ?? 0);
        [$displayPrice, $discountLabel] = $this->discountedPrice(
            $regularPrice,
            $product->discount_type,
            $product->discount_value,
            'percentage',
            'fixed'
        );

        return [
            'id' => 'cosmetic-' . $product->id,
            'name' => $product->name,
            'category_name' => $product->category?->name,
            'brand_name' => $product->brand?->name,
            'thumbnail_url' => $product->main_image_url,
            'hover_image_url' => $product->hover_image_url ?: $product->main_image_url,
            'regular_price' => $regularPrice,
            'display_price' => $displayPrice,
            'has_discount' => $displayPrice < $regularPrice,
            'discount_label' => $discountLabel,
            'is_sold_out' => $product->stock !== null && (int) $product->stock <= 0,
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'colors' => [],
            'url' => route('frontend.cosmetic-products.show', [
                'product' => filled($product->slug) ? $product->slug : $product->id,
            ]),
            'sort_key' => $product->id,
        ];
    }

    protected function fashionCard(FashionProduct $product): array
    {
        return $this->simpleCard(
            'fashion',
            $product->id,
            $product->name,
            $product->category?->name,
            $product->brand?->name ?: ($product->brand_name ?: $product->category?->name),
            $product->main_image_url,
            $product->hover_image_url,
            (float) ($product->price ?? 0),
            $product->sale_price !== null ? (float) $product->sale_price : null,
            $product->stock_status === 'out_of_stock' || ((int) ($product->stock_quantity ?? 0) <= 0),
            route('frontend.fashion.show', [
                'product' => filled($product->slug) ? $product->slug : $product->id,
            ])
        );
    }

    protected function homeNeedCard(HomeNeedProduct $product): array
    {
        return $this->simpleCard(
            'home-need',
            $product->id,
            $product->name,
            $product->category?->name,
            $product->brand?->name ?: $product->brand_name,
            $product->main_image_url,
            $product->hover_image_url,
            (float) ($product->price ?? 0),
            $product->sale_price !== null ? (float) $product->sale_price : null,
            $product->stock_status === 'out_of_stock' || ((int) ($product->stock_quantity ?? 0) <= 0),
            route('frontend.home-needs.show', [
                'product' => filled($product->slug) ? $product->slug : $product->id,
            ])
        );
    }

    protected function simpleCard(
        string $prefix,
        int|string $id,
        string $name,
        ?string $category,
        ?string $brand,
        ?string $imageUrl,
        ?string $hoverImageUrl,
        float $regularPrice,
        ?float $salePrice,
        bool $soldOut,
        string $url
    ): array {
        $displayPrice = $salePrice !== null && $salePrice > 0 ? $salePrice : $regularPrice;

        return [
            'id' => $prefix . '-' . $id,
            'name' => $name,
            'category_name' => $category,
            'brand_name' => $brand,
            'thumbnail_url' => $imageUrl,
            'hover_image_url' => $hoverImageUrl,
            'regular_price' => $regularPrice,
            'display_price' => $displayPrice,
            'has_discount' => $displayPrice < $regularPrice,
            'discount_label' => $this->discountLabel($regularPrice, $displayPrice),
            'is_sold_out' => $soldOut,
            'reviews_count' => 0,
            'reviews_avg_rating' => null,
            'colors' => [],
            'url' => $url,
            'sort_key' => (int) $id,
        ];
    }

    protected function discountedPrice(
        float $regularPrice,
        ?string $type,
        mixed $value,
        string $percentType = 'percent',
        string $fixedType = 'price'
    ): array {
        $discountValue = $value !== null ? (float) $value : null;

        if ($regularPrice <= 0 || $discountValue === null || $discountValue <= 0) {
            return [$regularPrice, null];
        }

        if ($type === $percentType) {
            $displayPrice = max(0, round($regularPrice - (($regularPrice * $discountValue) / 100), 2));

            return [$displayPrice, $this->discountLabel($regularPrice, $displayPrice)];
        }

        if ($type === $fixedType) {
            $displayPrice = max(0, round($regularPrice - $discountValue, 2));

            return [$displayPrice, $this->discountLabel($regularPrice, $displayPrice)];
        }

        return [$regularPrice, null];
    }

    protected function discountLabel(float $regularPrice, float $displayPrice): ?string
    {
        if ($regularPrice <= 0 || $displayPrice <= 0 || $displayPrice >= $regularPrice) {
            return null;
        }

        return '-' . (int) round((($regularPrice - $displayPrice) / $regularPrice) * 100) . '%';
    }

    protected function marketingFlagColumns(
        ?string $flag,
        string $table,
        array $dealsColumns,
        array $bestSellerColumns
    ): array {
        $candidates = match ($flag) {
            'deals' => $dealsColumns,
            'best_seller' => $bestSellerColumns,
            default => [],
        };

        return collect($candidates)
            ->unique()
            ->filter(fn (string $column) => $this->tableHasColumn($table, $column))
            ->values()
            ->all();
    }

    protected function whereAnyMarketingFlag($query, array $columns): void
    {
        $query->where(function ($flagQuery) use ($columns) {
            foreach ($columns as $column) {
                $flagQuery->orWhere($column, true);
            }
        });
    }

    protected function tableHasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;

        if (!array_key_exists($key, $this->schemaColumnCache)) {
            $this->schemaColumnCache[$key] = Schema::hasColumn($table, $column);
        }

        return $this->schemaColumnCache[$key];
    }

    protected function publicProducts(Collection $products): Collection
    {
        $kokoPayPercentage = KokoPaySetting::currentPercentage();

        return $products
            ->map(function (array $product) use ($kokoPayPercentage) {
                unset($product['sort_key']);

                $displayPrice = (float) ($product['display_price'] ?? 0);
                $product['koko_pay_percentage'] = KokoPay::percentage($kokoPayPercentage);
                $product['koko_installment_price'] = KokoPay::installmentAmount($displayPrice, $kokoPayPercentage);

                return $product;
            })
            ->values();
    }
}
