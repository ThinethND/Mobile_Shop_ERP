<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ShoeCategory;
use App\Models\ShoeProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebShoeProductController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Frontend/ShoeProductList/index', [
            'shoeCategories' => $this->shoeCategories(),
            'filters' => [
                'search' => trim((string) $request->query('search', '')),
                'shoe_category' => $request->query('shoe_category'),
                'shoe_subcategory' => $request->query('shoe_subcategory'),
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
        $activeShoeCategory = $request->query('shoe_category');
        $activeShoeSubcategory = $request->query('shoe_subcategory');
        $search = trim((string) $request->query('search', ''));
        $stock = trim((string) $request->query('stock', ''));
        $sort = trim((string) $request->query('sort', 'oldest'));
        $saleOnly = $request->boolean('sale');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        $normalizedShoeCategory = $this->normalize($activeShoeCategory);
        $normalizedShoeSubcategory = $this->normalize($activeShoeSubcategory);
        $normalizedSearch = $this->normalize($search);

        $today = now()->startOfDay();

        $query = ShoeProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
                'subcategory:id,name',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'published')
            ->when($normalizedShoeCategory, function ($query) use ($normalizedShoeCategory) {
                $query->whereHas('category', function ($categoryQuery) use ($normalizedShoeCategory) {
                    $categoryQuery->whereRaw('LOWER(name) = ?', [$normalizedShoeCategory]);
                });
            })
            ->when($normalizedShoeSubcategory, function ($query) use ($normalizedShoeSubcategory) {
                $query->whereHas('subcategory', function ($subcategoryQuery) use ($normalizedShoeSubcategory) {
                    $subcategoryQuery->whereRaw('LOWER(name) = ?', [$normalizedShoeSubcategory]);
                });
            })
            ->when($normalizedSearch, function ($query) use ($normalizedSearch) {
                $query->where(function ($inner) use ($normalizedSearch) {
                    $inner->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(slug) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(sku) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(model_code) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereHas('brand', function ($brandQuery) use ($normalizedSearch) {
                            $brandQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                        });
                });
            })
            ->when($stock === 'in_stock', function ($query) {
                $query->where(function ($inner) {
                    $inner->where(function ($q) {
                        $q->where('stock_status', '!=', 'out_of_stock')
                            ->orWhereNull('stock_status');
                    })->where(function ($q) {
                        $q->whereNull('stock_quantity')
                            ->orWhere('stock_quantity', '>', 0);
                    });
                });
            })
            ->when($stock === 'out_of_stock', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('stock_status', 'out_of_stock')
                        ->orWhere('status', 'out_of_stock')
                        ->orWhere(function ($q) {
                            $q->whereNotNull('stock_quantity')
                                ->where('stock_quantity', '<=', 0);
                        });
                });
            })
            ->when($saleOnly, function ($query) use ($today) {
                $query->whereNotNull('sale_price')
                    ->where('sale_price', '>', 0)
                    ->whereColumn('regular_price', '>', 'sale_price')
                    ->where(function ($q) use ($today) {
                        $q->whereNull('sale_start_date')
                            ->orWhereDate('sale_start_date', '<=', $today);
                    })
                    ->where(function ($q) use ($today) {
                        $q->whereNull('sale_end_date')
                            ->orWhereDate('sale_end_date', '>=', $today);
                    });
            })
            ->when(is_numeric($minPrice), function ($query) use ($minPrice) {
                $query->whereRaw('COALESCE(NULLIF(sale_price, 0), regular_price) >= ?', [(float) $minPrice]);
            })
            ->when(is_numeric($maxPrice), function ($query) use ($maxPrice) {
                $query->whereRaw('COALESCE(NULLIF(sale_price, 0), regular_price) <= ?', [(float) $maxPrice]);
            });

        switch ($sort) {
            case 'price_low_high':
                $query->orderByRaw('COALESCE(NULLIF(sale_price, 0), regular_price) asc')->orderBy('id');
                break;

            case 'price_high_low':
                $query->orderByRaw('COALESCE(NULLIF(sale_price, 0), regular_price) desc')->orderBy('id');
                break;

            case 'name_az':
                $query->orderBy('name')->orderBy('id');
                break;

            case 'name_za':
                $query->orderByDesc('name')->orderBy('id');
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

        $products = collect($paginator->items())
            ->map(fn (ShoeProduct $product) => $this->transformShoeProduct($product, $today))
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
                'shoe_category' => $activeShoeCategory,
                'shoe_subcategory' => $activeShoeSubcategory,
                'stock' => $stock,
                'sale' => $saleOnly,
                'sort' => $sort,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ],
        ]);
    }

    protected function transformShoeProduct(ShoeProduct $product, $today): array
    {
        $regularPrice = $product->regular_price !== null ? (float) $product->regular_price : null;
        $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;

        $saleStarted = !$product->sale_start_date || $product->sale_start_date->lte($today);
        $saleNotEnded = !$product->sale_end_date || $product->sale_end_date->gte($today);

        $hasActiveSale = $regularPrice !== null
            && $salePrice !== null
            && $salePrice > 0
            && $regularPrice > $salePrice
            && $saleStarted
            && $saleNotEnded;

        $isSoldOut = $product->status === 'out_of_stock'
            || $product->stock_status === 'out_of_stock'
            || ($product->stock_quantity !== null && (int) $product->stock_quantity <= 0);

        $discountLabel = null;

        if (!empty($product->discount_type) && $product->discount_value !== null) {
            if ($product->discount_type === 'percentage') {
                $discountLabel = 'Sale ' . rtrim(rtrim(number_format((float) $product->discount_value, 2), '0'), '.') . '%';
            } elseif ($product->discount_type === 'fixed') {
                $discountLabel = 'Sale LKR ' . number_format((float) $product->discount_value, 0);
            }
        } elseif ($hasActiveSale) {
            $discountPercent = (int) round((($regularPrice - $salePrice) / $regularPrice) * 100);
            if ($discountPercent > 0) {
                $discountLabel = 'Sale ' . $discountPercent . '%';
            }
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'brand_name' => $product->brand?->name,
            'category_name' => $product->category?->name,
            'subcategory_name' => $product->subcategory?->name,
            'thumbnail_url' => $product->thumbnail_url,
            'hover_image_url' => $product->hover_image_url,
            'currency' => $product->currency ?: 'LKR',
            'regular_price' => $regularPrice,
            'sale_price' => $hasActiveSale ? $salePrice : null,
            'display_price' => $hasActiveSale ? $salePrice : $regularPrice,
            'has_discount' => $hasActiveSale,
            'discount_label' => $discountLabel,
            'is_sold_out' => $isSoldOut,
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'status' => $product->status,
            'stock_status' => $product->stock_status,
        ];
    }

    protected function shoeCategories()
    {
        return ShoeCategory::query()
            ->where('status', 'active')
            ->with([
                'subcategories' => function ($query) {
                    $query->where('status', 'active')->oldest('id');
                }
            ])
            ->oldest('id')
            ->get()
            ->map(function (ShoeCategory $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image_url' => $category->image_url,
                    'status' => $category->status,
                    'subcategories' => $category->subcategories->map(function ($subcategory) {
                        return [
                            'id' => $subcategory->id,
                            'name' => $subcategory->name,
                            'image_url' => $subcategory->image_url,
                            'status' => $subcategory->status,
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
