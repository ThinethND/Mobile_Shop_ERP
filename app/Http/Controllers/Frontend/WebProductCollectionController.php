<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ColorOption;
use App\Models\CosmeticBrand;
use App\Models\CosmeticCategory;
use App\Models\CosmeticProduct;
use App\Models\CosmeticProductType;
use App\Models\FashionBrand;
use App\Models\FashionCategory;
use App\Models\FashionProduct;
use App\Models\FashionProductType;
use App\Models\HomeNeedBrand;
use App\Models\HomeNeedCategory;
use App\Models\HomeNeedProduct;
use App\Models\KokoPaySetting;
use App\Models\MotorcycleHelmetBrand;
use App\Models\MotorcycleProduct;
use App\Models\MotorcycleProductCategory;
use App\Models\Product;
use App\Support\KokoPay;
use App\Models\WarrantyOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebProductCollectionController extends Controller
{
    public function motorcycleIndex(Request $request): Response
    {
        return $this->renderCollection($request, 'motorcycle');
    }

    public function motorcycleProducts(Request $request): JsonResponse
    {
        return $this->products($request, 'motorcycle');
    }

    public function electronicsIndex(Request $request): Response
    {
        return $this->renderCollection($request, 'electronics');
    }

    public function electronicsProducts(Request $request): JsonResponse
    {
        return $this->products($request, 'electronics');
    }

    public function cosmeticsIndex(Request $request): Response
    {
        return $this->renderCollection($request, 'cosmetics');
    }

    public function cosmeticsProducts(Request $request): JsonResponse
    {
        return $this->products($request, 'cosmetics');
    }

    public function fashionIndex(Request $request): Response
    {
        return $this->renderCollection($request, 'fashion');
    }

    public function fashionProducts(Request $request): JsonResponse
    {
        return $this->products($request, 'fashion');
    }

    public function homeNeedsIndex(Request $request): Response
    {
        return $this->renderCollection($request, 'home-needs');
    }

    public function homeNeedsProducts(Request $request): JsonResponse
    {
        return $this->products($request, 'home-needs');
    }

    protected function renderCollection(Request $request, string $section): Response
    {
        $config = $this->sectionConfig($section);

        return Inertia::render('Frontend/ProductCollection/index', [
            'title' => $config['title'],
            'subtitle' => $config['subtitle'],
            'eyebrow' => $config['eyebrow'],
            'indexUrl' => route($config['index_route']),
            'productsUrl' => route($config['products_route']),
            'sectionKey' => $section,
            'brandLabel' => $config['brand_label'],
            'typeLabel' => $config['type_label'],
            'categories' => $this->categoryOptions($config),
            'brands' => $this->brandOptions($config),
            'types' => $this->typeOptions($config),
            'warranties' => $config['warranty_relation'] ? $this->warrantyOptions() : collect(),
            'filters' => $this->filters($request),
        ]);
    }

    protected function products(Request $request, string $section): JsonResponse
    {
        $config = $this->sectionConfig($section);
        $filters = $this->filters($request);
        $effectivePriceSql = $this->effectivePriceSql($config);

        $query = $config['model']::query()
            ->with($config['relations'])
            ->when($config['with_reviews'], fn ($query) => $query->withCount('reviews')->withAvg('reviews', 'rating'))
            ->where($config['status_column'], $config['status_value']);

        $this->applyFilters($query, $config, $filters, $effectivePriceSql);
        $this->applySort($query, $filters['sort'], $effectivePriceSql, $config['name_column']);

        $paginator = $query->paginate(12)->withQueryString();
        $items = collect($paginator->items());
        $context = [
            'koko_pay_percentage' => KokoPaySetting::currentPercentage(),
            'color_map' => $config['key'] === 'electronics'
                ? $this->colorMapForProducts($items)
                : collect(),
        ];

        $products = $items
            ->map(fn ($product) => $this->transformProduct($product, $config, $context))
            ->values();

        return response()
            ->json([
                'products' => $products,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem() ?? 0,
                    'to' => $paginator->lastItem() ?? 0,
                ],
                'filters' => $filters,
            ])
            ->header('Cache-Control', 'private, max-age=60');
    }

    protected function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'category' => $request->query('category', $request->query('cosmetic_category')),
            'brand' => $request->query('brand', $request->query('cosmetic_brand')),
            'type' => $request->query('type'),
            'warranty' => $request->query('warranty'),
            'stock' => $request->query('stock'),
            'sale' => $request->boolean('sale'),
            'featured' => $request->boolean('featured'),
            'today_best_deals' => $request->boolean('today_best_deals') || $request->boolean('hot_deals'),
            'best_seller' => $request->boolean('best_seller'),
            'sort' => $request->query('sort', 'latest'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'page' => max(1, (int) $request->query('page', 1)),
        ];
    }

    protected function applyFilters($query, array $config, array $filters, string $effectivePriceSql): void
    {
        $normalizedCategory = $this->normalize($filters['category']);
        $normalizedBrand = $this->normalize($filters['brand']);
        $normalizedType = $this->normalize($filters['type']);
        $normalizedWarranty = $this->normalize($filters['warranty']);
        $normalizedSearch = $this->normalize($filters['search']);
        $categoryRelation = $config['category_relation'];
        $brandRelation = $config['brand_relation'];
        $warrantyRelation = $config['warranty_relation'];

        if ($normalizedCategory) {
            $query->whereHas($categoryRelation, function ($categoryQuery) use ($normalizedCategory) {
                $categoryQuery->whereRaw('LOWER(name) = ?', [$normalizedCategory]);
            });
        }

        if ($normalizedBrand) {
            $query->where(function ($inner) use ($config, $brandRelation, $normalizedBrand) {
                $inner->whereHas($brandRelation, function ($brandQuery) use ($normalizedBrand) {
                    $brandQuery->whereRaw('LOWER(name) = ?', [$normalizedBrand]);
                });

                if ($config['brand_name_column']) {
                    $inner->orWhereRaw('LOWER(' . $config['brand_name_column'] . ') = ?', [$normalizedBrand]);
                }
            });
        }

        if ($normalizedType) {
            if ($config['type_column']) {
                $query->whereRaw('LOWER(' . $config['type_column'] . ') = ?', [$normalizedType]);
            } elseif ($config['type_relation']) {
                $query->whereHas($config['type_relation'], function ($typeQuery) use ($normalizedType) {
                    $typeQuery->whereRaw('LOWER(name) = ?', [$normalizedType]);
                });
            }
        }

        if ($normalizedWarranty && $warrantyRelation) {
            $query->whereHas($warrantyRelation, function ($warrantyQuery) use ($normalizedWarranty) {
                $warrantyQuery->whereRaw('LOWER(name) = ?', [$normalizedWarranty]);
            });
        }

        if ($normalizedSearch) {
            $query->where(function ($inner) use ($config, $categoryRelation, $brandRelation, $warrantyRelation, $normalizedSearch) {
                $inner->whereRaw('1 = 0');

                foreach ($config['search_columns'] as $column) {
                    $inner->orWhereRaw('LOWER(' . $column . ') like ?', ["%{$normalizedSearch}%"]);
                }

                $inner->orWhereHas($categoryRelation, function ($categoryQuery) use ($normalizedSearch) {
                    $categoryQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                });

                $inner->orWhereHas($brandRelation, function ($brandQuery) use ($normalizedSearch) {
                    $brandQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                });

                if ($warrantyRelation) {
                    $inner->orWhereHas($warrantyRelation, function ($warrantyQuery) use ($normalizedSearch) {
                        $warrantyQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                    });
                }

                if ($config['brand_name_column']) {
                    $inner->orWhereRaw('LOWER(' . $config['brand_name_column'] . ') like ?', ["%{$normalizedSearch}%"]);
                }

                if ($config['type_column']) {
                    $inner->orWhereRaw('LOWER(' . $config['type_column'] . ') like ?', ["%{$normalizedSearch}%"]);
                } elseif ($config['type_relation']) {
                    $inner->orWhereHas($config['type_relation'], function ($typeQuery) use ($normalizedSearch) {
                        $typeQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                    });
                }
            });
        }

        $this->applyStockFilter($query, $config, $filters['stock']);
        $this->applySaleFilter($query, $config, $filters['sale']);

        foreach ($config['flag_columns'] as $filterKey => $column) {
            if ($filters[$filterKey] && $column) {
                $query->where($column, true);
            }
        }

        if (is_numeric($filters['min_price'])) {
            $query->whereRaw("{$effectivePriceSql} >= ?", [(float) $filters['min_price']]);
        }

        if (is_numeric($filters['max_price'])) {
            $query->whereRaw("{$effectivePriceSql} <= ?", [(float) $filters['max_price']]);
        }
    }

    protected function applyStockFilter($query, array $config, ?string $stock): void
    {
        if ($stock === 'in_stock') {
            match ($config['stock_mode']) {
                'boolean_count' => $query->where('in_stock', true)
                    ->where(fn ($inner) => $inner->whereNull('stock_count')->orWhere('stock_count', '>', 0)),
                'nullable_quantity' => $query->where(fn ($inner) => $inner->whereNull('stock')->orWhere('stock', '>', 0)),
                default => $query->where('stock_status', 'in_stock')
                    ->where(fn ($inner) => $inner->whereNull('stock_quantity')->orWhere('stock_quantity', '>', 0)),
            };
        }

        if ($stock === 'out_of_stock') {
            match ($config['stock_mode']) {
                'boolean_count' => $query->where(fn ($inner) => $inner->where('in_stock', false)
                    ->orWhere(fn ($stockQuery) => $stockQuery->whereNotNull('stock_count')->where('stock_count', '<=', 0))),
                'nullable_quantity' => $query->whereNotNull('stock')->where('stock', '<=', 0),
                default => $query->where(fn ($inner) => $inner->where('stock_status', 'out_of_stock')
                    ->orWhere(fn ($stockQuery) => $stockQuery->whereNotNull('stock_quantity')->where('stock_quantity', '<=', 0))),
            };
        }
    }

    protected function applySaleFilter($query, array $config, bool $saleOnly): void
    {
        if (!$saleOnly) {
            return;
        }

        match ($config['sale_mode']) {
            'electronics_discount' => $query->whereNotNull('discount_value')
                ->where('discount_value', '>', 0)
                ->whereIn('discount_type', ['percent', 'price']),
            'cosmetic_discount' => $query->whereNotNull('discount_type')
                ->whereNotNull('discount_value')
                ->where('discount_value', '>', 0)
                ->where('price', '>', 0),
            default => $query->whereNotNull($config['sale_price_column'])
                ->where($config['sale_price_column'], '>', 0)
                ->whereColumn($config['sale_price_column'], '<', $config['regular_price_column']),
        };
    }

    protected function applySort($query, ?string $sort, string $effectivePriceSql, string $nameColumn): void
    {
        match ($sort) {
            'price_low_high' => $query->orderByRaw("{$effectivePriceSql} asc")->orderBy('id'),
            'price_high_low' => $query->orderByRaw("{$effectivePriceSql} desc")->orderBy('id'),
            'name_az' => $query->orderBy($nameColumn)->orderBy('id'),
            'name_za' => $query->orderByDesc($nameColumn)->orderBy('id'),
            'oldest' => $query->oldest('id'),
            default => $query->latest('id'),
        };
    }

    protected function effectivePriceSql(array $config): string
    {
        if ($config['price_mode'] === 'electronics_discount') {
            return "
                CASE
                    WHEN discount_type = 'percent' AND discount_value IS NOT NULL AND discount_value > 0 AND price_lkr > 0
                        THEN GREATEST(price_lkr - ((price_lkr * discount_value) / 100), 0)
                    WHEN discount_type = 'price' AND discount_value IS NOT NULL AND discount_value > 0 AND price_lkr > 0
                        THEN GREATEST(price_lkr - discount_value, 0)
                    ELSE price_lkr
                END
            ";
        }

        if ($config['price_mode'] === 'cosmetic_discount') {
            return "
                CASE
                    WHEN discount_type = 'percentage' AND discount_value IS NOT NULL AND discount_value > 0 AND price > 0
                        THEN GREATEST(price - ((price * discount_value) / 100), 0)
                    WHEN discount_type = 'fixed' AND discount_value IS NOT NULL AND discount_value > 0 AND price > 0
                        THEN GREATEST(price - discount_value, 0)
                    ELSE price
                END
            ";
        }

        $regularColumn = $config['regular_price_column'];
        $saleColumn = $config['sale_price_column'];

        return "CASE WHEN {$saleColumn} IS NOT NULL AND {$saleColumn} > 0 AND {$regularColumn} IS NOT NULL AND {$saleColumn} < {$regularColumn} THEN {$saleColumn} ELSE COALESCE({$regularColumn}, 0) END";
    }

    protected function transformProduct($product, array $config, array $context): array
    {
        return match ($config['key']) {
            'electronics' => $this->transformElectronicsProduct($product, $context['color_map'], $context['koko_pay_percentage']),
            'cosmetics' => $this->transformCosmeticProduct($product, $context['koko_pay_percentage']),
            default => $this->transformModuleProduct($product, $config, $context['koko_pay_percentage']),
        };
    }

    protected function transformModuleProduct($product, array $config, float $kokoPayPercentage): array
    {
        $regularPrice = (float) ($product->{$config['regular_price_column']} ?? 0);
        $salePrice = $product->{$config['sale_price_column']} !== null ? (float) $product->{$config['sale_price_column']} : null;
        $displayPrice = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice
            ? $salePrice
            : $regularPrice;
        $galleryUrls = collect($product->gallery_urls ?? [])->filter()->values();
        $thumbnailUrl = $product->main_image_url ?: $galleryUrls->first();
        $hoverImageUrl = data_get($product, 'hover_image_url')
            ?: ($galleryUrls->first(fn ($url) => $url !== $thumbnailUrl) ?: null);
        $categoryName = $product->{$config['category_relation']}?->name;
        $brandName = $product->{$config['brand_relation']}?->name
            ?: ($config['brand_name_column'] ? $product->{$config['brand_name_column']} : null);
        $brandName = $brandName ?: ($config['key'] === 'home-needs' ? $categoryName : null);

        return [
            'id' => $config['key'] . '-' . $product->id,
            'name' => $product->{$config['name_column']} ?? '',
            'category_name' => $categoryName,
            'brand_name' => $brandName,
            'short_description' => $product->short_description,
            'thumbnail_url' => $thumbnailUrl,
            'hover_image_url' => $hoverImageUrl,
            'regular_price' => $regularPrice,
            'display_price' => $displayPrice,
            'koko_pay_percentage' => KokoPay::percentage($kokoPayPercentage),
            'koko_installment_price' => KokoPay::installmentAmount($displayPrice, $kokoPayPercentage),
            'has_discount' => $displayPrice < $regularPrice,
            'discount_label' => $this->discountLabel($regularPrice, $displayPrice),
            'is_sold_out' => $product->stock_status === 'out_of_stock'
                || ($product->stock_quantity !== null && (int) $product->stock_quantity <= 0),
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'colors' => [],
            'product_type' => $config['key'],
            'url' => route($config['show_route'], [
                'product' => $this->routeIdentifier($product),
            ]),
        ];
    }

    protected function transformElectronicsProduct(Product $product, $colorMap, float $kokoPayPercentage): array
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
            'short_description' => $product->short_description,
            'thumbnail_url' => $product->main_image_url,
            'hover_image_url' => $product->hover_image_url,
            'regular_price' => $regularPrice,
            'display_price' => $displayPrice,
            'koko_pay_percentage' => KokoPay::percentage($kokoPayPercentage),
            'koko_installment_price' => KokoPay::installmentAmount($displayPrice, $kokoPayPercentage),
            'has_discount' => $displayPrice < $regularPrice,
            'discount_label' => $discountLabel,
            'is_sold_out' => !$product->in_stock || ($product->stock_count !== null && (int) $product->stock_count <= 0),
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'colors' => $colors,
            'url' => route('frontend.tech-products.show', ['product' => $this->routeIdentifier($product)]),
        ];
    }

    protected function transformCosmeticProduct(CosmeticProduct $product, float $kokoPayPercentage): array
    {
        $regularPrice = (float) ($product->price ?? 0);
        [$displayPrice, $discountLabel] = $this->discountedPrice(
            $regularPrice,
            $product->discount_type,
            $product->discount_value,
            'percentage',
            'fixed'
        );
        $galleryUrls = collect($product->gallery_urls ?? [])->filter()->values();
        $thumbnailUrl = $product->main_image_url ?: $galleryUrls->first();
        $hoverImageUrl = $product->hover_image_url
            ?: ($galleryUrls->first(fn ($url) => $url !== $thumbnailUrl) ?: $thumbnailUrl);

        return [
            'id' => 'cosmetic-' . $product->id,
            'name' => $product->name,
            'category_name' => $product->category?->name,
            'brand_name' => $product->brand?->name,
            'short_description' => $product->short_description,
            'thumbnail_url' => $thumbnailUrl,
            'hover_image_url' => $hoverImageUrl,
            'regular_price' => $regularPrice,
            'display_price' => $displayPrice,
            'koko_pay_percentage' => KokoPay::percentage($kokoPayPercentage),
            'koko_installment_price' => KokoPay::installmentAmount($displayPrice, $kokoPayPercentage),
            'has_discount' => $displayPrice < $regularPrice,
            'discount_label' => $discountLabel,
            'is_sold_out' => $product->stock !== null && (int) $product->stock <= 0,
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'colors' => [],
            'url' => route('frontend.cosmetic-products.show', [
                'product' => $this->routeIdentifier($product),
            ]),
        ];
    }

    protected function routeIdentifier(object $product): int|string
    {
        return filled($product->slug ?? null) ? $product->slug : $product->id;
    }

    protected function colorMapForProducts($products)
    {
        $colorIds = $products
            ->flatMap(fn (Product $product) => collect($product->color_ids ?? [])->map(fn ($id) => (int) $id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        return ColorOption::query()
            ->select('id', 'name', 'color_code')
            ->whereIn('id', $colorIds)
            ->get()
            ->keyBy('id');
    }

    protected function categoryOptions(array $config)
    {
        $query = $config['category_model']::query();

        if ($config['category_status_column']) {
            $query->where($config['category_status_column'], 'active');
        }

        return $query
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->values();
    }

    protected function brandOptions(array $config)
    {
        $query = $config['brand_model']::query();

        if ($config['brand_status_column']) {
            $query->where($config['brand_status_column'], 'active');
        }

        return $query
            ->orderBy('name')
            ->get(['id', 'name', 'logo_path'])
            ->map(fn ($brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'logo_url' => $brand->logo_url ?? null,
            ])
            ->values();
    }

    protected function typeOptions(array $config)
    {
        if ($config['key'] === 'motorcycle') {
            return collect(MotorcycleProduct::TYPES)
                ->map(fn (string $type) => [
                    'id' => $type,
                    'name' => str($type)->replace('_', ' ')->title()->toString(),
                    'value' => $type,
                ])
                ->values();
        }

        if (!$config['type_model']) {
            return collect();
        }

        $query = $config['type_model']::query();

        if ($config['type_status_column']) {
            $query->where($config['type_status_column'], 'active');
        }

        return $query
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'value' => $type->name,
            ])
            ->values();
    }

    protected function warrantyOptions()
    {
        return WarrantyOption::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (WarrantyOption $warranty) => [
                'id' => $warranty->id,
                'name' => $warranty->name,
            ])
            ->values();
    }

    protected function sectionConfig(string $section): array
    {
        $baseModule = [
            'category_relation' => 'category',
            'sale_price_column' => 'sale_price',
            'status_column' => 'status',
            'status_value' => 'active',
            'price_mode' => 'sale_price',
            'sale_mode' => 'sale_price',
            'stock_mode' => 'stock_status_quantity',
            'with_reviews' => false,
            'category_status_column' => 'status',
            'brand_status_column' => 'status',
            'type_status_column' => 'status',
            'warranty_relation' => 'warrantyOption',
            'flag_columns' => [
                'featured' => 'featured',
                'today_best_deals' => 'today_best_deals',
                'best_seller' => 'best_seller',
            ],
            'search_columns' => ['name', 'slug', 'sku', 'short_description'],
        ];

        return match ($section) {
            'motorcycle' => array_merge($baseModule, [
                'key' => 'motorcycle',
                'title' => 'Motorcycle Products',
                'subtitle' => 'Helmets, riding accessories, bike parts, and useful gear with fast category filtering.',
                'eyebrow' => 'Ride Essentials',
                'model' => MotorcycleProduct::class,
                'category_model' => MotorcycleProductCategory::class,
                'brand_model' => MotorcycleHelmetBrand::class,
                'type_model' => null,
                'relations' => ['category:id,name', 'helmetBrand:id,name,logo_path,status', 'warrantyOption:id,name'],
                'brand_relation' => 'helmetBrand',
                'brand_name_column' => null,
                'type_relation' => null,
                'type_column' => 'product_type',
                'regular_price_column' => 'regular_price',
                'name_column' => 'name',
                'index_route' => 'frontend.motorcycle-products.index',
                'products_route' => 'frontend.motorcycle-products.products',
                'show_route' => 'frontend.motorcycle-products.show',
                'brand_label' => 'Helmet Brand',
                'type_label' => 'Product Type',
                'with_reviews' => true,
            ]),
            'electronics' => array_merge($baseModule, [
                'key' => 'electronics',
                'title' => 'Electronics',
                'subtitle' => 'Phones, watches, earbuds, accessories, and tech picks with the same fast filtering layout.',
                'eyebrow' => 'Tech Collection',
                'model' => Product::class,
                'category_model' => Category::class,
                'brand_model' => Brand::class,
                'type_model' => null,
                'relations' => ['category:id,name', 'brand:id,name,logo_path,status', 'warrantyOption:id,name'],
                'brand_relation' => 'brand',
                'brand_name_column' => null,
                'type_relation' => null,
                'type_column' => null,
                'regular_price_column' => 'price_lkr',
                'name_column' => 'model',
                'index_route' => 'frontend.tech-products.index',
                'products_route' => 'frontend.tech-products.products',
                'show_route' => 'frontend.tech-products.show',
                'brand_label' => 'Brand',
                'type_label' => '',
                'price_mode' => 'electronics_discount',
                'sale_mode' => 'electronics_discount',
                'stock_mode' => 'boolean_count',
                'with_reviews' => true,
                'flag_columns' => [
                    'featured' => 'is_featured',
                    'today_best_deals' => 'is_deal_of_the_day',
                    'best_seller' => 'is_best_seller',
                ],
                'search_columns' => ['model', 'slug', 'sku', 'os', 'short_description'],
            ]),
            'cosmetics' => array_merge($baseModule, [
                'key' => 'cosmetics',
                'title' => 'Cosmetics',
                'subtitle' => 'Beauty essentials and personal care picks organized with the same clean filters and cards.',
                'eyebrow' => 'Beauty Shelf',
                'model' => CosmeticProduct::class,
                'category_model' => CosmeticCategory::class,
                'brand_model' => CosmeticBrand::class,
                'type_model' => CosmeticProductType::class,
                'relations' => ['category:id,name,slug', 'brand:id,name,slug,logo_path,status', 'productType:id,name', 'countryOfOrigin:id,name,code,flag_image_path'],
                'brand_relation' => 'brand',
                'brand_name_column' => null,
                'type_relation' => 'productType',
                'type_column' => null,
                'regular_price_column' => 'price',
                'name_column' => 'name',
                'index_route' => 'frontend.cosmetic-products.index',
                'products_route' => 'frontend.cosmetic-products.products',
                'show_route' => 'frontend.cosmetic-products.show',
                'brand_label' => 'Brand',
                'type_label' => 'Product Type',
                'price_mode' => 'cosmetic_discount',
                'sale_mode' => 'cosmetic_discount',
                'stock_mode' => 'nullable_quantity',
                'with_reviews' => true,
                'category_status_column' => null,
                'type_status_column' => null,
                'warranty_relation' => null,
                'flag_columns' => [
                    'featured' => 'is_featured',
                    'today_best_deals' => 'hot_deals',
                    'best_seller' => 'best_selling',
                ],
                'search_columns' => ['name', 'slug', 'batch_number', 'short_description'],
            ]),
            'fashion' => array_merge($baseModule, [
                'key' => 'fashion',
                'title' => 'Fashion & Accessories',
                'subtitle' => 'Necklaces, bags, fashion wear, sunglasses, and accessories arranged for quick discovery.',
                'eyebrow' => 'Style Edit',
                'model' => FashionProduct::class,
                'category_model' => FashionCategory::class,
                'brand_model' => FashionBrand::class,
                'type_model' => FashionProductType::class,
                'relations' => ['category:id,name', 'brand:id,name,logo_path,status', 'productType:id,name', 'warrantyOption:id,name'],
                'brand_relation' => 'brand',
                'brand_name_column' => 'brand_name',
                'type_relation' => 'productType',
                'type_column' => null,
                'regular_price_column' => 'price',
                'name_column' => 'name',
                'index_route' => 'frontend.fashion.index',
                'products_route' => 'frontend.fashion.products',
                'show_route' => 'frontend.fashion.show',
                'brand_label' => 'Brand',
                'type_label' => 'Product Type',
            ]),
            'home-needs' => array_merge($baseModule, [
                'key' => 'home-needs',
                'title' => 'Home Needs',
                'subtitle' => 'Everyday home, kitchen, storage, decor, and utility products in one clean shopping page.',
                'eyebrow' => 'For Every Room',
                'model' => HomeNeedProduct::class,
                'category_model' => HomeNeedCategory::class,
                'brand_model' => HomeNeedBrand::class,
                'type_model' => null,
                'relations' => ['category:id,name', 'brand:id,name,logo_path,status', 'warrantyOption:id,name'],
                'brand_relation' => 'brand',
                'brand_name_column' => 'brand_name',
                'type_relation' => null,
                'type_column' => null,
                'regular_price_column' => 'price',
                'name_column' => 'name',
                'index_route' => 'frontend.home-needs.index',
                'products_route' => 'frontend.home-needs.products',
                'show_route' => 'frontend.home-needs.show',
                'brand_label' => 'Brand',
                'type_label' => '',
            ]),
            default => abort(404),
        };
    }

    protected function discountedPrice(
        float $regularPrice,
        ?string $discountType,
        $discountValue,
        string $percentType = 'percent',
        string $fixedType = 'price'
    ): array {
        $displayPrice = $regularPrice;
        $discountLabel = null;
        $discountValue = $discountValue !== null ? (float) $discountValue : null;

        if ($discountValue !== null && $discountValue > 0 && $regularPrice > 0) {
            if ($discountType === $percentType) {
                $displayPrice = max(0, round($regularPrice - (($regularPrice * $discountValue) / 100), 2));
                $discountLabel = 'Sale ' . rtrim(rtrim(number_format($discountValue, 2), '0'), '.') . '%';
            } elseif ($discountType === $fixedType) {
                $displayPrice = max(0, round($regularPrice - $discountValue, 2));
                $discountLabel = 'Sale Rs ' . number_format($discountValue, 0);
            }
        }

        if ($displayPrice >= $regularPrice) {
            return [$regularPrice, null];
        }

        return [$displayPrice, $discountLabel];
    }

    protected function discountLabel(float $regularPrice, float $displayPrice): ?string
    {
        if ($regularPrice <= 0 || $displayPrice <= 0 || $displayPrice >= $regularPrice) {
            return null;
        }

        return '-' . (int) round((($regularPrice - $displayPrice) / $regularPrice) * 100) . '%';
    }

    protected function normalize($value): ?string
    {
        if (!filled($value)) {
            return null;
        }

        return mb_strtolower(trim((string) $value));
    }
}
