<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ColorOption;
use App\Models\HomeBanner;
use App\Models\HomeNeedCategory;
use App\Models\FashionCategory;
use App\Models\MotorcycleProductCategory;
use App\Models\Product;
use App\Models\ShoeCategory;
use App\Models\ShoeProduct;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;
use App\Models\CosmeticProduct;
use App\Models\CosmeticBrand;
use App\Models\CosmeticCategory;

class HomeController extends Controller
{
    public function index()
    {
        $activeCategory = request('category');
        $activeBrand = request('brand');
        $activeShoeCategory = request('shoe_category');
        $activeShoeSubcategory = request('shoe_subcategory');
        $search = trim((string) request('search', ''));

       $banners = HomeBanner::query()
    ->latest()
    ->get()
    ->map(function (HomeBanner $b) {
        return [
            'id' => $b->id,
            'name' => $b->name,
            'description' => $b->description,
            'desktop_image_url' => $b->desktop_image_url,
            'mobile_image_url' => $b->mobile_image_url,
            'video_url' => $b->video_url, // legacy fallback
        ];
    })
    ->values();

        return Inertia::render('Frontend/Home/index', [
            'products' => [],
            'activeCategory' => $activeCategory,
            'activeBrand' => $activeBrand,
            'banners' => $banners,
            'categories' => [],
            'shoeCategories' => [],
            'featuredShoes' => [],
            'search' => $search,
            'activeShoeCategory' => $activeShoeCategory,
            'activeShoeSubcategory' => $activeShoeSubcategory,
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
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
                    'brands' => $category->brands->map(function ($brand) {
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

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function allCategories(): JsonResponse
    {
        $groups = [
            [
                'key' => 'electronics',
                'label' => 'Electronics',
                'url' => route('frontend.tech-products.index'),
                'items' => $this->categoryItems(
                    Category::query()
                        ->where('status', 'active')
                        ->oldest('id')
                        ->get(['id', 'name']),
                    fn (string $name) => route('frontend.tech-products.index', ['category' => $name])
                ),
            ],
            [
                'key' => 'motorcycle',
                'label' => 'Motorcycle Products',
                'url' => route('frontend.motorcycle-products.index'),
                'items' => $this->categoryItems(
                    MotorcycleProductCategory::query()
                        ->where('status', 'active')
                        ->oldest('id')
                        ->get(['id', 'name']),
                    fn (string $name) => route('frontend.motorcycle-products.index', ['category' => $name])
                ),
            ],
            [
                'key' => 'cosmetics',
                'label' => 'Cosmetics',
                'url' => route('frontend.cosmetic-products.index'),
                'items' => $this->categoryItems(
                    CosmeticCategory::query()
                        ->orderBy('name')
                        ->get(['id', 'name']),
                    fn (string $name) => route('frontend.cosmetic-products.index', ['cosmetic_category' => $name])
                ),
            ],
            [
                'key' => 'fashion',
                'label' => 'Fashion',
                'url' => route('frontend.fashion.index'),
                'items' => $this->categoryItems(
                    FashionCategory::query()
                        ->where('status', 'active')
                        ->orderBy('name')
                        ->get(['id', 'name']),
                    fn (string $name) => route('frontend.fashion.index', ['category' => $name])
                ),
            ],
            [
                'key' => 'home-needs',
                'label' => 'Home Needs',
                'url' => route('frontend.home-needs.index'),
                'items' => $this->categoryItems(
                    HomeNeedCategory::query()
                        ->where('status', 'active')
                        ->orderBy('name')
                        ->get(['id', 'name']),
                    fn (string $name) => route('frontend.home-needs.index', ['category' => $name])
                ),
            ],
        ];

        return response()->json([
            'groups' => $groups,
        ])->header('Cache-Control', 'private, max-age=60');
    }

    protected function categoryItems($categories, callable $urlBuilder)
    {
        return $categories
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'url' => $urlBuilder($category->name),
            ])
            ->values();
    }

    public function shoeCategories(): JsonResponse
    {
        $shoeCategories = ShoeCategory::query()
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

        return response()->json([
            'categories' => $shoeCategories,
        ]);
    }

    public function featuredShoes(): JsonResponse
    {
        $activeShoeCategory = request('shoe_category');
        $activeShoeSubcategory = request('shoe_subcategory');
        $search = trim((string) request('search', ''));

        $normalizedShoeCategory = filled($activeShoeCategory)
            ? mb_strtolower(trim((string) $activeShoeCategory))
            : null;

        $normalizedShoeSubcategory = filled($activeShoeSubcategory)
            ? mb_strtolower(trim((string) $activeShoeSubcategory))
            : null;

        $normalizedSearch = filled($search)
            ? mb_strtolower($search)
            : null;

        $today = now()->startOfDay();

        $featuredShoes = ShoeProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
                'subcategory:id,name',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('featured', true)
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
                        ->orWhereRaw('LOWER(sku) like ?', ["%{$normalizedSearch}%"]);
                });
            })
            ->oldest('id')
            ->take(8)
            ->get()
            ->map(function (ShoeProduct $product) use ($today) {
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
            })
            ->values();

        return response()->json([
            'products' => $featuredShoes,
            'activeShoeCategory' => $activeShoeCategory,
            'activeShoeSubcategory' => $activeShoeSubcategory,
            'search' => $search,
        ]);
    }

    public function products(): JsonResponse
    {
        $activeCategory = request('category');
        $activeBrand = request('brand');
        $search = trim((string) request('search', ''));

        $normalizedCategory = filled($activeCategory)
            ? mb_strtolower(trim((string) $activeCategory))
            : null;

        $normalizedBrand = filled($activeBrand)
            ? mb_strtolower(trim((string) $activeBrand))
            : null;

        $normalizedSearch = filled($search)
            ? mb_strtolower($search)
            : null;

        $baseProducts = Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->when($normalizedCategory, function ($query, $normalizedCategory) {
                $query->whereHas('category', function ($categoryQuery) use ($normalizedCategory) {
                    $categoryQuery->whereRaw('LOWER(name) = ?', [$normalizedCategory]);
                });
            })
            ->when($normalizedBrand, function ($query, $normalizedBrand) {
                $query->whereHas('brand', function ($brandQuery) use ($normalizedBrand) {
                    $brandQuery->whereRaw('LOWER(name) = ?', [$normalizedBrand]);
                });
            })
            ->when($normalizedSearch, function ($query) use ($normalizedSearch) {
                $query->where(function ($inner) use ($normalizedSearch) {
                    $inner->whereRaw('LOWER(model) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(sku) like ?', ["%{$normalizedSearch}%"]);
                });
            })
            ->oldest('id')
            ->take(8)
            ->get();

        $colorIds = $baseProducts
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

        $products = $baseProducts
            ->map(function (Product $product) use ($colorMap) {
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
                            'image_url' => $color->image_url,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'id' => $product->id,
                    'name' => $product->model ?? '',
                    'category_name' => $product->category?->name,
                    'brand_name' => $product->brand?->name,
                    'thumbnail_url' => $product->main_image_url,
                    'hover_image_url' => $product->hover_image_url,
                    'regular_price' => $regularPrice,
                    'display_price' => $displayPrice,
                    'has_discount' => $hasDiscount,
                    'discount_label' => $discountLabel,
                    'is_sold_out' => $isSoldOut,
                    'reviews_count' => (int) ($product->reviews_count ?? 0),
                    'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
                    'colors' => $colors,
                ];
            })
            ->values();

        return response()->json([
            'products' => $products,
            'activeCategory' => $activeCategory,
            'activeBrand' => $activeBrand,
        ]);
    }

    public function featuredProducts(): JsonResponse
{
    $activeCategory = request('category');
    $activeBrand = request('brand');
    $search = trim((string) request('search', ''));

    $normalizedCategory = filled($activeCategory)
        ? mb_strtolower(trim((string) $activeCategory))
        : null;

    $normalizedBrand = filled($activeBrand)
        ? mb_strtolower(trim((string) $activeBrand))
        : null;

    $normalizedSearch = filled($search)
        ? mb_strtolower($search)
        : null;

    $featuredColumn = null;

    if (Schema::hasColumn('products', 'featured')) {
        $featuredColumn = 'featured';
    } elseif (Schema::hasColumn('products', 'is_featured')) {
        $featuredColumn = 'is_featured';
    }

    $query = Product::query()
        ->with([
            'category:id,name',
            'brand:id,name',
        ])
        ->withCount('reviews')
        ->withAvg('reviews', 'rating')
        ->where('status', 'active')
        ->when($featuredColumn, function ($query) use ($featuredColumn) {
            $query->where($featuredColumn, true);
        })
        ->when(!$featuredColumn, function ($query) {
            $query->whereRaw('1 = 0');
        })
        ->when($normalizedCategory, function ($query, $normalizedCategory) {
            $query->whereHas('category', function ($categoryQuery) use ($normalizedCategory) {
                $categoryQuery->whereRaw('LOWER(name) = ?', [$normalizedCategory]);
            });
        })
        ->when($normalizedBrand, function ($query, $normalizedBrand) {
            $query->whereHas('brand', function ($brandQuery) use ($normalizedBrand) {
                $brandQuery->whereRaw('LOWER(name) = ?', [$normalizedBrand]);
            });
        })
        ->when($normalizedSearch, function ($query) use ($normalizedSearch) {
            $query->where(function ($inner) use ($normalizedSearch) {
                $inner->whereRaw('LOWER(model) like ?', ["%{$normalizedSearch}%"])
                    ->orWhereRaw('LOWER(sku) like ?', ["%{$normalizedSearch}%"]);
            });
        });

    $baseProducts = $query
        ->oldest('id')
        ->take(5)
        ->get();

    $colorIds = $baseProducts
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

    $products = $baseProducts
        ->map(function (Product $product) use ($colorMap) {
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
                        'image_url' => $color->image_url,
                    ];
                })
                ->filter()
                ->values()
                ->all();

            return [
                'id' => $product->id,
                'name' => $product->model ?? '',
                'category_name' => $product->category?->name,
                'brand_name' => $product->brand?->name,
                'thumbnail_url' => $product->main_image_url,
                'hover_image_url' => $product->hover_image_url,
                'regular_price' => $regularPrice,
                'display_price' => $displayPrice,
                'has_discount' => $hasDiscount,
                'discount_label' => $discountLabel,
                'is_sold_out' => $isSoldOut,
                'reviews_count' => (int) ($product->reviews_count ?? 0),
                'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
                'colors' => $colors,
            ];
        })
        ->values();

    return response()->json([
        'products' => $products,
        'activeCategory' => $activeCategory,
        'activeBrand' => $activeBrand,
        'search' => $search,
    ]);

    }

    public function featuredCosmetics(): JsonResponse
    {
        $search = trim((string) request('search', ''));

        $normalizedSearch = filled($search) ? mb_strtolower($search) : null;

        $featured = CosmeticProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
                'countryOfOrigin:id,name,code,flag_image_path',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->where('is_featured', true)
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
                        });
                });
            })
            ->oldest('id')
            ->take(8)
            ->get()
            ->map(function (CosmeticProduct $product) {
                $regularPrice = $product->price !== null ? (float) $product->price : null;
                $display = $regularPrice;
                $discountLabel = null;

                if (!empty($product->discount_type) && $product->discount_value !== null) {
                    $val = (float) $product->discount_value;
                    if ($product->discount_type === 'percentage') {
                        $display = max(0, round($regularPrice - (($regularPrice * $val) / 100), 2));
                        $discountLabel = 'Sale ' . rtrim(rtrim(number_format($val, 2), '0'), '.') . '%';
                    } else {
                        $display = max(0, round($regularPrice - $val, 2));
                        $discountLabel = 'Sale LKR ' . number_format($val, 0);
                    }
                }

                $hasDiscount = $display < $regularPrice;

                $isSoldOut = $product->stock !== null && (int) $product->stock <= 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'category_name' => $product->category?->name,
                    'brand_name' => $product->brand?->name,
                    'country_name' => $product->countryOfOrigin?->name,
                    'country_code' => $product->countryOfOrigin?->code,
                    'country_flag_url' => $product->countryOfOrigin?->flag_image_url,
                    'thumbnail_url' => $product->main_image_url,
                    'hover_image_url' => $product->hover_image_url ?: $product->main_image_url,
                    'currency' => 'LKR',
                    'regular_price' => $regularPrice,
                    'sale_price' => $hasDiscount ? $display : null,
                    'display_price' => $display,
                    'has_discount' => $hasDiscount,
                    'discount_label' => $discountLabel,
                    'is_sold_out' => $isSoldOut,
                    'reviews_count' => (int) ($product->reviews_count ?? 0),
                    'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
                    'status' => $product->status,
                    'url' => route('frontend.cosmetic-products.show', [
                        'product' => filled($product->slug) ? $product->slug : $product->id,
                    ]),
                ];
            })
            ->values();

        return response()->json([
            'products' => $featured,
            'search' => $search,
        ]);
    }

    public function cosmeticBrands(): JsonResponse
    {
        $brands = CosmeticBrand::query()
            ->where('status', 'active')
            ->whereHas('products', function ($query) {
                $query->where('status', 'active');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'logo_path', 'status'])
            ->map(fn (CosmeticBrand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'logo_url' => $brand->logo_url,
                'status' => $brand->status,
            ])
            ->values();

        return response()->json([
            'brands' => $brands,
        ]);
    }

    public function cosmeticProducts(): JsonResponse
    {
        $activeBrand = request('brand');
        $search = trim((string) request('search', ''));

        $normalizedBrand = filled($activeBrand)
            ? mb_strtolower(trim((string) $activeBrand))
            : null;

        $normalizedSearch = filled($search)
            ? mb_strtolower($search)
            : null;

        $products = CosmeticProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
                'countryOfOrigin:id,name,code,flag_image_path',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->when($normalizedBrand, function ($query) use ($normalizedBrand) {
                $query->whereHas('brand', function ($brandQuery) use ($normalizedBrand) {
                    $brandQuery->whereRaw('LOWER(name) = ?', [$normalizedBrand]);
                });
            })
            ->when($normalizedSearch, function ($query) use ($normalizedSearch) {
                $query->where(function ($inner) use ($normalizedSearch) {
                    $inner->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(slug) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(batch_number) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereHas('brand', function ($brandQuery) use ($normalizedSearch) {
                            $brandQuery->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"]);
                        });
                });
            })
            ->oldest('id')
            ->take(8)
            ->get()
            ->map(function (CosmeticProduct $product) {
                $regularPrice = $product->price !== null ? (float) $product->price : null;
                $displayPrice = $regularPrice;
                $discountLabel = null;

                if (!empty($product->discount_type) && $product->discount_value !== null && $regularPrice !== null) {
                    $val = (float) $product->discount_value;

                    if ($val > 0 && $regularPrice > 0) {
                        if ($product->discount_type === 'percentage') {
                            $displayPrice = max(0, round($regularPrice - (($regularPrice * $val) / 100), 2));
                            $discountLabel = 'Sale ' . rtrim(rtrim(number_format($val, 2), '0'), '.') . '%';
                        } elseif ($product->discount_type === 'fixed') {
                            $displayPrice = max(0, round($regularPrice - $val, 2));
                            $discountLabel = 'Sale LKR ' . number_format($val, 0);
                        }
                    }
                }

                $hasDiscount = $regularPrice !== null && $displayPrice !== null && $displayPrice < $regularPrice;

                if (!$hasDiscount) {
                    $displayPrice = $regularPrice;
                    $discountLabel = null;
                }

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
                    'thumbnail_url' => $product->main_image_url,
                    'hover_image_url' => $product->hover_image_url ?: $product->main_image_url,
                    'regular_price' => $regularPrice,
                    'display_price' => $displayPrice,
                    'has_discount' => $hasDiscount,
                    'discount_label' => $discountLabel,
                    'is_sold_out' => $isSoldOut,
                    'reviews_count' => (int) ($product->reviews_count ?? 0),
                    'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
                    'url' => route('frontend.cosmetic-products.show', [
                        'product' => filled($product->slug) ? $product->slug : $product->id,
                    ]),
                ];
            })
            ->values();

        return response()->json([
            'products' => $products,
            'activeBrand' => $activeBrand,
            'search' => $search,
        ]);
    }



}
