<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CosmeticBrand;
use App\Models\CosmeticCategory;
use App\Models\CosmeticCountryOfOrigin;
use App\Models\CosmeticProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebCosmeticProductController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Frontend/CosmeticProductList/index', [
            'categories' => $this->cosmeticCategories(),
            'countries' => $this->cosmeticCountries(),
            'filters' => [
                'search' => trim((string) $request->query('search', '')),
                'cosmetic_category' => $request->query('cosmetic_category'),
                'cosmetic_brand' => $request->query('cosmetic_brand'),
                'cosmetic_country' => $request->query('cosmetic_country'),
                'stock' => $request->query('stock'),
                'sale' => $request->boolean('sale'),
                'sort' => $request->query('sort', 'oldest'),
                'min_price' => $request->query('min_price'),
                'max_price' => $request->query('max_price'),
                'page' => max(1, (int) $request->query('page', 1)),
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $activeCategory = $request->query('cosmetic_category');
        $activeBrand = $request->query('cosmetic_brand');
        $activeCountry = $request->query('cosmetic_country');
        $search = trim((string) $request->query('search', ''));
        $stock = trim((string) $request->query('stock', ''));
        $sort = trim((string) $request->query('sort', 'oldest'));
        $saleOnly = $request->boolean('sale');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        $normalizedCategory = $this->normalize($activeCategory);
        $normalizedBrand = $this->normalize($activeBrand);
        $normalizedCountry = $this->normalize($activeCountry);
        $normalizedSearch = $this->normalize($search);

        $effectivePriceSql = "
            CASE
                WHEN discount_type = 'percentage' AND discount_value IS NOT NULL AND discount_value > 0 AND price > 0
                    THEN GREATEST(price - ((price * discount_value) / 100), 0)
                WHEN discount_type = 'fixed' AND discount_value IS NOT NULL AND discount_value > 0 AND price > 0
                    THEN GREATEST(price - discount_value, 0)
                ELSE price
            END
        ";

        $query = CosmeticProduct::query()
            ->with([
                'brand:id,name,slug,logo_path,status',
                'category:id,name,slug',
                'countryOfOrigin:id,name,code,flag_image_path',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->when($normalizedCategory, function ($query) use ($normalizedCategory) {
                $query->whereHas('category', function ($categoryQuery) use ($normalizedCategory) {
                    $categoryQuery->whereRaw('LOWER(name) = ?', [$normalizedCategory]);
                });
            })
            ->when($normalizedBrand, function ($query) use ($normalizedBrand) {
                $query->whereHas('brand', function ($brandQuery) use ($normalizedBrand) {
                    $brandQuery->whereRaw('LOWER(name) = ?', [$normalizedBrand]);
                });
            })
            ->when($normalizedCountry, function ($query) use ($normalizedCountry) {
                $query->whereHas('countryOfOrigin', function ($countryQuery) use ($normalizedCountry) {
                    $countryQuery->where(function ($inner) use ($normalizedCountry) {
                        $inner->whereRaw('LOWER(name) = ?', [$normalizedCountry])
                            ->orWhereRaw('LOWER(code) = ?', [$normalizedCountry]);
                    });
                });
            })
            ->when($normalizedSearch, function ($query) use ($normalizedSearch) {
                $query->where(function ($inner) use ($normalizedSearch) {
                    $inner->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(slug) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(batch_number) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereHas('brand', function ($brandQuery) use ($normalizedSearch) {
                            $brandQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                        })
                        ->orWhereHas('category', function ($categoryQuery) use ($normalizedSearch) {
                            $categoryQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                        })
                        ->orWhereHas('productType', function ($typeQuery) use ($normalizedSearch) {
                            $typeQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                        })
                        ->orWhereHas('variants', function ($variantQuery) use ($normalizedSearch) {
                            $variantQuery->whereRaw('LOWER(sku) like ?', ["%{$normalizedSearch}%"]);
                        });
                });
            })
            ->when($stock === 'in_stock', function ($query) {
                $query->where(function ($inner) {
                    $inner->whereNull('stock')
                        ->orWhere('stock', '>', 0);
                });
            })
            ->when($stock === 'out_of_stock', function ($query) {
                $query->whereNotNull('stock')
                    ->where('stock', '<=', 0);
            })
            ->when($saleOnly, function ($query) {
                $query->whereNotNull('discount_type')
                    ->whereNotNull('discount_value')
                    ->where('discount_value', '>', 0)
                    ->where('price', '>', 0);
            })
            ->when(is_numeric($minPrice), function ($query) use ($minPrice, $effectivePriceSql) {
                $query->whereRaw("{$effectivePriceSql} >= ?", [(float) $minPrice]);
            })
            ->when(is_numeric($maxPrice), function ($query) use ($maxPrice, $effectivePriceSql) {
                $query->whereRaw("{$effectivePriceSql} <= ?", [(float) $maxPrice]);
            });

        if ($sort === 'price_low_high') {
            $query->orderByRaw("{$effectivePriceSql} asc")->orderBy('id');
        } elseif ($sort === 'price_high_low') {
            $query->orderByRaw("{$effectivePriceSql} desc")->orderBy('id');
        } elseif ($sort === 'name_az') {
            $query->orderBy('name')->orderBy('id');
        } elseif ($sort === 'name_za') {
            $query->orderByDesc('name')->orderBy('id');
        } elseif ($sort === 'latest') {
            $query->latest('id');
        } else {
            $query->oldest('id');
        }

        $paginator = $query->paginate(12)->withQueryString();

        $products = collect($paginator->items())
            ->map(fn (CosmeticProduct $product) => $this->transformCosmeticProduct($product))
            ->values();

        return response()->json([
            'products' => $products,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
            ],
            'filters' => [
                'search' => $search,
                'cosmetic_category' => $activeCategory,
                'cosmetic_brand' => $activeBrand,
                'cosmetic_country' => $activeCountry,
                'stock' => $stock,
                'sale' => $saleOnly,
                'sort' => $sort,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ],
        ]);
    }

    protected function transformCosmeticProduct(CosmeticProduct $product): array
    {
        $product->loadMissing(['brand', 'category', 'countryOfOrigin']);

        $regularPrice = $product->price !== null ? (float) $product->price : null;
        $discount = $this->resolveDiscount($regularPrice, $product->discount_type, $product->discount_value);

        $galleryUrls = collect($product->gallery_urls ?? [])->filter()->values();

        $thumbnailUrl = $product->main_image_url ?: $galleryUrls->first();
        $hoverImageUrl = $product->hover_image_url
            ?: $galleryUrls->first(fn ($url) => $url !== $thumbnailUrl)
            ?: $thumbnailUrl;

        $stockCount = $product->stock !== null ? (int) $product->stock : null;
        $isSoldOut = $stockCount !== null && $stockCount <= 0;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'brand_name' => $product->brand?->name,
            'category_name' => $product->category?->name,
            'country_name' => $product->countryOfOrigin?->name,
            'country_code' => $product->countryOfOrigin?->code,
            'country_flag_url' => $product->countryOfOrigin?->flag_image_url,
            'thumbnail_url' => $thumbnailUrl,
            'hover_image_url' => $hoverImageUrl,
            'currency' => 'LKR',
            'regular_price' => $discount['old_price'] ?? $discount['current_price'],
            'sale_price' => $discount['has_discount'] ? $discount['current_price'] : null,
            'display_price' => $discount['current_price'],
            'has_discount' => $discount['has_discount'],
            'discount_label' => $discount['label'],
            'is_sold_out' => $isSoldOut,
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'url' => route('frontend.cosmetic-products.show', [
                'product' => filled($product->slug) ? $product->slug : $product->id,
            ]),
        ];
    }

    protected function cosmeticCategories()
    {
        $pairs = CosmeticProduct::query()
            ->select(['category_id', 'brand_id'])
            ->where('status', 'active')
            ->whereNotNull('category_id')
            ->whereNotNull('brand_id')
            ->distinct()
            ->get();

        $categoryIds = $pairs->pluck('category_id')->filter()->unique()->values();
        $brandIds = $pairs->pluck('brand_id')->filter()->unique()->values();

        $categories = CosmeticCategory::query()
            ->whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->keyBy('id');

        $brands = CosmeticBrand::query()
            ->whereIn('id', $brandIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'logo_path', 'status'])
            ->keyBy('id');

        $categoryBrandMap = [];

        foreach ($pairs as $pair) {
            $categoryId = (int) $pair->category_id;
            $brandId = (int) $pair->brand_id;

            if (!isset($categoryBrandMap[$categoryId])) {
                $categoryBrandMap[$categoryId] = [];
            }

            $categoryBrandMap[$categoryId][$brandId] = true;
        }

        return $categories
            ->values()
            ->map(function (CosmeticCategory $category) use ($categoryBrandMap, $brands) {
                $brandIdSet = $categoryBrandMap[(int) $category->id] ?? [];

                $categoryBrands = collect(array_keys($brandIdSet))
                    ->map(fn ($brandId) => $brands->get((int) $brandId))
                    ->filter()
                    ->sortBy('name')
                    ->map(fn (CosmeticBrand $brand) => [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'logo_url' => $brand->logo_url,
                        'status' => $brand->status,
                    ])
                    ->values();

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'brands' => $categoryBrands,
                ];
            })
            ->filter(fn (array $category) => count($category['brands'] ?? []) > 0)
            ->values();
    }

    protected function cosmeticCountries()
    {
        return CosmeticCountryOfOrigin::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'flag_image_path'])
            ->map(fn (CosmeticCountryOfOrigin $country) => [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'flag_image_url' => $country->flag_image_url,
            ])
            ->values();
    }

    protected function resolveDiscount(?float $price, ?string $discountType, $discountValue): array
    {
        $price = $price !== null ? max(0, round($price, 2)) : 0.0;
        $discountValue = is_numeric($discountValue) ? (float) $discountValue : 0.0;

        if ($discountValue <= 0 || !in_array($discountType, ['percentage', 'fixed'], true) || $price <= 0) {
            return [
                'old_price' => null,
                'current_price' => $price,
                'has_discount' => false,
                'label' => null,
            ];
        }

        if ($discountType === 'percentage') {
            $currentPrice = max(0, round($price - (($price * $discountValue) / 100), 2));
            $label = 'Sale ' . rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.') . '%';
        } else {
            $currentPrice = max(0, round($price - $discountValue, 2));
            $label = 'Sale LKR ' . number_format($discountValue, 0);
        }

        return [
            'old_price' => $currentPrice < $price ? $price : null,
            'current_price' => $currentPrice,
            'has_discount' => $currentPrice < $price,
            'label' => $currentPrice < $price ? $label : null,
        ];
    }

    protected function normalize($value): ?string
    {
        if (!filled($value)) {
            return null;
        }

        return mb_strtolower(trim((string) $value));
    }
}
