<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ColorOption;
use App\Models\KokoPaySetting;
use App\Models\Product;
use App\Support\KokoPay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebTechProductController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Frontend/TechProductList/index', [
            'categories' => $this->techCategories(),
            'filters' => [
                'search' => trim((string) $request->query('search', '')),
                'category' => $request->query('category'),
                'brand' => $request->query('brand'),
                'stock' => $request->query('stock'),
                'sale' => $request->boolean('sale'),
                'hot_deals' => $request->boolean('hot_deals'),
                'featured' => $request->boolean('featured'),
                'best_seller' => $request->boolean('best_seller'),
                'top_rated' => $request->boolean('top_rated'),
                'sort' => $request->query('sort', 'oldest'),
                'min_price' => $request->query('min_price'),
                'max_price' => $request->query('max_price'),
                'page' => max(1, (int) $request->query('page', 1)),
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $activeCategory = $request->query('category');
        $activeBrand = $request->query('brand');
        $search = trim((string) $request->query('search', ''));
        $stock = trim((string) $request->query('stock', ''));
        $sort = trim((string) $request->query('sort', 'oldest'));
        $saleOnly = $request->boolean('sale');
        $hotDealsOnly = $request->boolean('hot_deals');
        $featuredOnly = $request->boolean('featured');
        $bestSellerOnly = $request->boolean('best_seller');
        $topRatedOnly = $request->boolean('top_rated');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        $normalizedCategory = $this->normalize($activeCategory);
        $normalizedBrand = $this->normalize($activeBrand);
        $normalizedSearch = $this->normalize($search);

        $effectivePriceSql = "
            CASE
                WHEN discount_type = 'percent' AND discount_value IS NOT NULL AND discount_value > 0 AND price_lkr > 0
                    THEN GREATEST(price_lkr - ((price_lkr * discount_value) / 100), 0)
                WHEN discount_type = 'price' AND discount_value IS NOT NULL AND discount_value > 0 AND price_lkr > 0
                    THEN GREATEST(price_lkr - discount_value, 0)
                ELSE price_lkr
            END
        ";

        $query = Product::query()
            ->with([
                'category:id,name',
                'brand:id,name,logo_path',
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
            ->when($normalizedSearch, function ($query) use ($normalizedSearch) {
                $query->where(function ($inner) use ($normalizedSearch) {
                    $inner->whereRaw('LOWER(model) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(sku) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(os) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(short_description) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereHas('brand', function ($brandQuery) use ($normalizedSearch) {
                            $brandQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                        })
                        ->orWhereHas('category', function ($categoryQuery) use ($normalizedSearch) {
                            $categoryQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                        });
                });
            })
            ->when($stock === 'in_stock', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('in_stock', true)
                        ->where(function ($q) {
                            $q->whereNull('stock_count')
                                ->orWhere('stock_count', '>', 0);
                        });
                });
            })
            ->when($stock === 'out_of_stock', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('in_stock', false)
                        ->orWhere(function ($q) {
                            $q->whereNotNull('stock_count')
                                ->where('stock_count', '<=', 0);
                        });
                });
            })
            ->when($saleOnly, function ($query) {
                $query->whereNotNull('discount_value')
                    ->where('discount_value', '>', 0)
                    ->whereIn('discount_type', ['percent', 'price']);
            })
            ->when($hotDealsOnly, function ($query) {
                $query->where('is_deal_of_the_day', true);
            })
            ->when($featuredOnly, function ($query) {
                $query->where('is_featured', true);
            })
            ->when($bestSellerOnly, function ($query) {
                $query->where('is_best_seller', true);
            })
            ->when($topRatedOnly, function ($query) {
                $query->where('is_top_rated', true);
            })
            ->when(is_numeric($minPrice), function ($query) use ($effectivePriceSql, $minPrice) {
                $query->whereRaw("{$effectivePriceSql} >= ?", [(float) $minPrice]);
            })
            ->when(is_numeric($maxPrice), function ($query) use ($effectivePriceSql, $maxPrice) {
                $query->whereRaw("{$effectivePriceSql} <= ?", [(float) $maxPrice]);
            });

        switch ($sort) {
            case 'price_low_high':
                $query->orderByRaw("{$effectivePriceSql} asc")->orderBy('id');
                break;

            case 'price_high_low':
                $query->orderByRaw("{$effectivePriceSql} desc")->orderBy('id');
                break;

            case 'name_az':
                $query->orderBy('model')->orderBy('id');
                break;

            case 'name_za':
                $query->orderByDesc('model')->orderBy('id');
                break;

            case 'latest':
                $query->latest('id');
                break;

            case 'oldest':
            default:
                $query->oldest('id');
                break;
        }

        $paginator = $query->paginate(12)->withQueryString();

        $items = collect($paginator->items());

        $colorIds = $items
            ->flatMap(function (Product $product) {
                return collect($product->color_ids ?? [])->map(fn ($id) => (int) $id);
            })
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $colorMap = ColorOption::query()
            ->select('id', 'name', 'color_code')
            ->whereIn('id', $colorIds)
            ->get()
            ->keyBy('id');

        $kokoPayPercentage = KokoPaySetting::currentPercentage();

        $products = $items
            ->map(fn (Product $product) => $this->transformProduct($product, $colorMap, $kokoPayPercentage))
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
                'category' => $activeCategory,
                'brand' => $activeBrand,
                'stock' => $stock,
                'sale' => $saleOnly,
                'hot_deals' => $hotDealsOnly,
                'featured' => $featuredOnly,
                'best_seller' => $bestSellerOnly,
                'top_rated' => $topRatedOnly,
                'sort' => $sort,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ],
        ]);
    }

    public function cartRelated(Request $request): JsonResponse
    {
        $productIds = collect($request->input('product_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return response()->json([
                'products' => [],
            ]);
        }

        $categoryIds = Product::query()
            ->whereIn('id', $productIds)
            ->pluck('category_id')
            ->filter()
            ->unique()
            ->values();

        $sameCategoryProducts = Product::query()
            ->with(['category:id,name', 'brand:id,name,logo_path'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $productIds)
            ->oldest('id')
            ->take(8)
            ->get();

        $fallbackProducts = collect();

        if ($sameCategoryProducts->count() < 4) {
            $excludeIds = $sameCategoryProducts->pluck('id')
                ->merge($productIds)
                ->unique()
                ->values();

            $fallbackProducts = Product::query()
                ->with(['category:id,name', 'brand:id,name,logo_path'])
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->where('status', 'active')
                ->whereNotIn('id', $excludeIds)
                ->oldest('id')
                ->take(8)
                ->get();
        }

        $products = $sameCategoryProducts
            ->concat($fallbackProducts)
            ->unique('id')
            ->take(4)
            ->values();

        $colorIds = $products
            ->flatMap(function (Product $product) {
                return collect($product->color_ids ?? [])->map(fn ($id) => (int) $id);
            })
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $colorMap = ColorOption::query()
            ->select('id', 'name', 'color_code')
            ->whereIn('id', $colorIds)
            ->get()
            ->keyBy('id');

        $kokoPayPercentage = KokoPaySetting::currentPercentage();

        return response()->json([
            'products' => $products
                ->map(fn (Product $product) => $this->transformProduct($product, $colorMap, $kokoPayPercentage))
                ->values(),
        ]);
    }

    protected function transformProduct(Product $product, $colorMap, float $kokoPayPercentage): array
    {
        $regularPrice = (float) ($product->price_lkr ?? 0);
        $displayPrice = $regularPrice;
        $discountLabel = null;

        $discountType = $product->discount_type;
        $discountValue = $product->discount_value !== null ? (float) $product->discount_value : null;

        if ($discountValue !== null && $discountValue > 0 && $regularPrice > 0) {
            if ($discountType === 'percent') {
                $displayPrice = max(0, round($regularPrice - (($regularPrice * $discountValue) / 100), 2));
                $discountLabel = 'Sale ' . rtrim(rtrim(number_format($discountValue, 2), '0'), '.') . '%';
            } elseif ($discountType === 'price') {
                $displayPrice = max(0, round($regularPrice - $discountValue, 2));
                $discountLabel = 'Sale Rs ' . number_format($discountValue, 0);
            }
        }

        $hasDiscount = $displayPrice < $regularPrice;

        if (!$hasDiscount) {
            $displayPrice = $regularPrice;
            $discountLabel = null;
        }

        $isSoldOut = !$product->in_stock
            || ($product->stock_count !== null && (int) $product->stock_count <= 0);

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
                    'image_url' => $color->image_url ?? null,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $product->id,
            'name' => $product->model ?? '',
            'slug' => $product->slug,
            'category_name' => $product->category?->name,
            'brand_name' => $product->brand?->name,
            'thumbnail_url' => $product->main_image_url,
            'hover_image_url' => $product->hover_image_url,
            'regular_price' => $regularPrice,
            'display_price' => $displayPrice,
            'koko_pay_percentage' => KokoPay::percentage($kokoPayPercentage),
            'koko_installment_price' => KokoPay::installmentAmount($displayPrice, $kokoPayPercentage),
            'has_discount' => $hasDiscount,
            'discount_label' => $discountLabel,
            'is_sold_out' => $isSoldOut,
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'colors' => $colors,
            'url' => route('frontend.tech-products.show', ['product' => $product->routeIdentifier()]),
        ];
    }

    protected function techCategories()
    {
        return Category::query()
            ->where('status', 'active')
            ->with([
                'brands' => function ($query) {
                    $query->where('brands.status', 'active')
                        ->orderBy('brands.name')
                        ->select('brands.id', 'brands.name', 'brands.logo_path', 'brands.status');
                }
            ])
            ->oldest('id')
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image_url' => $category->image_url,
                    'status' => $category->status,
                    'brands' => $category->brands->map(function (Brand $brand) {
                        return [
                            'id' => $brand->id,
                            'name' => $brand->name,
                            'logo_url' => $brand->logo_url,
                            'status' => $brand->status,
                        ];
                    })->values(),
                ];
            })
            ->values();
    }

    protected function normalize($value): ?string
    {
        if (!filled($value)) {
            return null;
        }

        return mb_strtolower(trim((string) $value));
    }
}
