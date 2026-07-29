<?php

//ModuleProductViewController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FashionProduct;
use App\Models\HomeNeedProduct;
use App\Models\Invoice;
use App\Models\KokoPaySetting;
use App\Models\MotorcycleProduct;
use App\Models\MotorcycleProductReview;
use App\Models\Order;
use App\Support\KokoPay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
//checkk

class ModuleProductViewController extends Controller
{
    public function motorcycleIndex(string $product): Response|RedirectResponse
    {
        return $this->renderProduct('motorcycle', $product);
    }

    public function motorcycleData(string $product): JsonResponse
    {
        return $this->productData('motorcycle', $product);
    }

    public function motorcycleReviews(Request $request, string $product): JsonResponse
    {
        $motorcycleProduct = $this->findProduct($this->sectionConfig('motorcycle'), $product);

        abort_if(!$motorcycleProduct, 404);

        $motorcycleProduct->loadCount('reviews')->loadAvg('reviews', 'rating');

        $sort = (string) $request->query('sort', 'recent');
        $sort = in_array($sort, ['recent', 'highest', 'lowest'], true) ? $sort : 'recent';

        $limit = max(1, min((int) $request->query('limit', 4), 4));
        $offset = max(0, (int) $request->query('offset', 0));

        $reviewsQuery = $motorcycleProduct->reviews()->where('status', 'active');

        if ($sort === 'highest') {
            $reviewsQuery->orderByRaw('rating is null')->orderByDesc('rating')->orderByDesc('id');
        } elseif ($sort === 'lowest') {
            $reviewsQuery->orderByRaw('rating is null')->orderBy('rating')->orderByDesc('id');
        } else {
            $reviewsQuery->orderByDesc('id');
        }

        $reviews = $reviewsQuery
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn (MotorcycleProductReview $review) => $this->reviewPayload($review))
            ->values();

        $returned = $reviews->count();
        $total = (int) ($motorcycleProduct->reviews_count ?? 0);

        return response()->json([
            'summary' => [
                'reviews_count' => $total,
                'avg_rating' => $motorcycleProduct->reviews_avg_rating !== null
                    ? (float) $motorcycleProduct->reviews_avg_rating
                    : null,
            ],
            'reviews' => $reviews,
            'pagination' => [
                'offset' => $offset,
                'limit' => $limit,
                'returned' => $returned,
                'has_more' => ($offset + $returned) < $total,
            ],
        ]);
    }

    public function storeMotorcycleReview(Request $request, string $product): JsonResponse
    {
        $motorcycleProduct = $this->findProduct($this->sectionConfig('motorcycle'), $product);

        abort_if(!$motorcycleProduct, 404);

        $validated = $request->validate([
            'invoice_no' => ['required', 'string', 'regex:/^INV-\\d+$/', 'max:50'],
            'anonymous' => ['nullable', 'boolean'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'long_description' => ['nullable', 'string', 'max:5000'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $invoiceExists = Invoice::query()->where('invoice_no', $validated['invoice_no'])->exists()
            || Order::query()->where('order_number', $validated['invoice_no'])->exists();

        if (!$invoiceExists) {
            throw ValidationException::withMessages([
                'invoice_no' => 'The invoice ID you entered was not found in our system. Please check and try again.',
            ]);
        }

        $imagePaths = $request->hasFile('images')
            ? collect($request->file('images'))
                ->map(fn ($file) => $file->store('motorcycle/reviews', 'public'))
                ->values()
                ->all()
            : [];

        $review = MotorcycleProductReview::create([
            'product_id' => $motorcycleProduct->id,
            'rating' => (int) $validated['rating'],
            'customer_name' => (bool) ($validated['anonymous'] ?? false)
                ? null
                : ($validated['customer_name'] ?? null),
            'customer_email' => null,
            'short_description' => null,
            'long_description' => filled($validated['long_description'] ?? null)
                ? $validated['long_description']
                : null,
            'image_paths' => $imagePaths,
            'status' => 'active',
        ]);

        $motorcycleProduct->loadCount('reviews')->loadAvg('reviews', 'rating');

        return response()->json([
            'message' => 'Review submitted successfully.',
            'summary' => [
                'reviews_count' => (int) ($motorcycleProduct->reviews_count ?? 0),
                'avg_rating' => $motorcycleProduct->reviews_avg_rating !== null
                    ? (float) $motorcycleProduct->reviews_avg_rating
                    : null,
            ],
            'review' => $this->reviewPayload($review),
        ], 201);
    }

    public function fashionIndex(string $product): Response|RedirectResponse
    {
        return $this->renderProduct('fashion', $product);
    }

    public function fashionData(string $product): JsonResponse
    {
        return $this->productData('fashion', $product);
    }

    public function homeNeedsIndex(string $product): Response|RedirectResponse
    {
        return $this->renderProduct('home-needs', $product);
    }

    public function homeNeedsData(string $product): JsonResponse
    {
        return $this->productData('home-needs', $product);
    }

    private function renderProduct(string $section, string $identifier): Response|RedirectResponse
    {
        $config = $this->sectionConfig($section);
        $product = $this->findProduct($config, $identifier);

        abort_if(!$product, 404);

        if ($redirect = $this->canonicalRedirectResponse($identifier, $product, $config['show_route'])) {
            return $redirect;
        }

        return Inertia::render('Frontend/module_productview/index', [
            'sectionKey' => $section,
            'productKey' => $identifier,
            'dataUrl' => route($config['data_route'], ['product' => $identifier]),
            'shell' => [
                'name' => $this->productName($product, $config),
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => $config['title'], 'href' => route($config['index_route'])],
                    ['label' => $this->productName($product, $config), 'href' => null],
                ],
            ],
        ]);
    }

    private function productData(string $section, string $identifier): JsonResponse
    {
        $config = $this->sectionConfig($section);
        $product = $this->findProduct($config, $identifier);

        abort_if(!$product, 404);

        $product->load($config['relations']);

        if ($config['reviews']) {
            $product->loadCount('reviews')->loadAvg('reviews', 'rating');
        }

        $kokoPayPercentage = KokoPaySetting::currentPercentage();

        return response()->json([
            'product' => $this->productPayload($product, $config, $kokoPayPercentage),
            'related_products' => $this->relatedProducts($product, $config, $kokoPayPercentage),
        ])->header('Cache-Control', 'private, max-age=90');
    }

    private function findProduct(array $config, string $identifier): ?Model
    {
        return $config['model']::query()
            ->where('status', 'active')
            ->where(function ($query) use ($identifier) {
                if (ctype_digit($identifier)) {
                    $query->whereKey((int) $identifier)->orWhere('slug', $identifier);
                    return;
                }

                $query->where('slug', $identifier);
            })
            ->first();
    }

    private function productPayload(Model $product, array $config, float $kokoPayPercentage): array
    {
        $pricing = $this->pricing($product, $config);
        $gallery = $this->gallery($product);
        $mainImage = $product->main_image_url ?: ($gallery->first()['src'] ?? null);
        $stockCount = $product->stock_quantity !== null ? (int) $product->stock_quantity : 0;
        $inStock = $product->stock_status !== 'out_of_stock' && $stockCount > 0;
        $brand = $this->brand($product, $config);

        return [
            'id' => $product->id,
            'product_type' => $config['key'],
            'name' => $this->productName($product, $config),
            'slug' => $product->slug,
            'sku' => $product->sku,
            'short_description' => $product->short_description,
            'long_description' => $product->{$config['long_description_column']},
            'brand' => [
                'id' => $brand?->id,
                'name' => $brand?->name
                    ?: ($config['brand_name_column'] ? $product->{$config['brand_name_column']} : null)
                    ?: ($config['key'] === 'home-needs' ? $product->category?->name : null),
                'logo_url' => $this->brandLogoUrl($brand),
            ],
            'category' => [
                'id' => $product->category?->id,
                'name' => $product->category?->name,
            ],
            'product_type_label' => $this->productTypeLabel($product, $config),
            'breadcrumb' => [
                ['label' => 'Home', 'href' => '/'],
                ['label' => $config['title'], 'href' => route($config['index_route'])],
                ['label' => $this->productName($product, $config), 'href' => null],
            ],
            'main_image' => $mainImage,
            'gallery' => $gallery,
            'base_price' => $pricing['base_price'],
            'old_price' => $pricing['old_price'],
            'current_price' => $pricing['current_price'],
            'has_discount' => $pricing['has_discount'],
            'discount_label' => $pricing['discount_label'],
            'koko_pay_percentage' => KokoPay::percentage($kokoPayPercentage),
            'koko_installment_price' => KokoPay::installmentAmount($pricing['current_price'], $kokoPayPercentage),
            'stock_count' => $stockCount,
            'in_stock' => $inStock,
            'warranty_label' => $this->warrantyLabel($product),
            'color' => $product->color ?? null,
            'size_label' => $product->size_label ?? null,
            'unit_label' => $product->unit_label ?? null,
            'variant_label' => $this->variantLabel($product, $config),
            'specifications' => $this->specifications($product, $config),
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
        ];
    }

    private function relatedProducts(Model $product, array $config, float $kokoPayPercentage)
    {
        if (!$product->category_id) {
            return collect();
        }

        $query = $config['model']::query()
            ->with($config['relations'])
            ->where('status', 'active')
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->oldest('id')
            ->take(4);

        if ($config['reviews']) {
            $query->withCount('reviews')->withAvg('reviews', 'rating');
        }

        return $query
            ->get()
            ->map(fn (Model $item) => $this->productCard($item, $config, $kokoPayPercentage))
            ->values();
    }

    private function productCard(Model $product, array $config, float $kokoPayPercentage): array
    {
        $pricing = $this->pricing($product, $config);
        $galleryUrls = collect($product->gallery_urls ?? [])->filter()->values();
        $thumbnailUrl = $product->main_image_url ?: $galleryUrls->first();
        $hoverImageUrl = data_get($product, 'hover_image_url')
            ?: ($galleryUrls->first(fn ($url) => $url !== $thumbnailUrl) ?: null);
        $stockCount = $product->stock_quantity !== null ? (int) $product->stock_quantity : 0;
        $brand = $this->brand($product, $config);

        return [
            'id' => $config['card_prefix'] . '-' . $product->id,
            'product_type' => $config['key'],
            'name' => $this->productName($product, $config),
            'category_name' => $product->category?->name,
            'brand_name' => $brand?->name
                ?: ($config['brand_name_column'] ? $product->{$config['brand_name_column']} : null)
                ?: ($config['key'] === 'home-needs' ? $product->category?->name : null),
            'short_description' => $product->short_description,
            'thumbnail_url' => $thumbnailUrl,
            'hover_image_url' => $hoverImageUrl,
            'regular_price' => $pricing['base_price'],
            'display_price' => $pricing['current_price'],
            'koko_pay_percentage' => KokoPay::percentage($kokoPayPercentage),
            'koko_installment_price' => KokoPay::installmentAmount($pricing['current_price'], $kokoPayPercentage),
            'has_discount' => $pricing['has_discount'],
            'discount_label' => $pricing['discount_label'],
            'is_sold_out' => $product->stock_status === 'out_of_stock' || $stockCount <= 0,
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'colors' => [],
            'url' => route($config['show_route'], ['product' => $this->routeIdentifier($product)]),
        ];
    }

    private function pricing(Model $product, array $config): array
    {
        $regularPrice = (float) ($product->{$config['price_column']} ?? 0);
        $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;
        $currentPrice = $salePrice !== null && $salePrice > 0 ? $salePrice : $regularPrice;
        $hasDiscount = $regularPrice > 0 && $currentPrice > 0 && $currentPrice < $regularPrice;

        return [
            'base_price' => $regularPrice,
            'old_price' => $hasDiscount ? $regularPrice : null,
            'current_price' => $currentPrice,
            'has_discount' => $hasDiscount,
            'discount_label' => $hasDiscount
                ? '-' . (int) round((($regularPrice - $currentPrice) / $regularPrice) * 100) . '%'
                : null,
        ];
    }

    private function gallery(Model $product)
    {
        return collect([$product->main_image_url, ...($product->gallery_urls ?? [])])
            ->filter()
            ->unique()
            ->take(6)
            ->values()
            ->map(fn ($url, $index) => [
                'id' => 'gallery-' . $index,
                'src' => $url,
            ])
            ->values();
    }

    private function specifications(Model $product, array $config)
    {
        $rows = collect($config['spec_columns'])
            ->map(function (array $row) use ($product) {
                $value = $this->resolveSpecValue($product, $row['column']);

                return [
                    'label' => $row['label'],
                    'value' => $value,
                ];
            })
            ->filter(fn (array $row) => filled($row['value']))
            ->values();

        if ($config['key'] === 'motorcycle') {
            $rows = $rows->merge($this->jsonSpecificationRows($product->specifications ?? []));
        }

        return $rows->values();
    }

    private function jsonSpecificationRows(mixed $specifications)
    {
        if (!is_array($specifications)) {
            return collect();
        }

        return collect($specifications)
            ->map(function ($value, $key) {
                if (is_array($value)) {
                    $label = $value['label'] ?? $value['name'] ?? $key;
                    $resolved = $value['value'] ?? $value['description'] ?? null;
                } else {
                    $label = $key;
                    $resolved = $value;
                }

                if (is_int($label)) {
                    $label = 'Specification';
                }

                return [
                    'label' => str((string) $label)->replace('_', ' ')->title()->toString(),
                    'value' => is_scalar($resolved) ? (string) $resolved : null,
                ];
            })
            ->filter(fn (array $row) => filled($row['value']))
            ->values();
    }

    private function resolveSpecValue(Model $product, string $column): ?string
    {
        $value = data_get($product, $column);

        if (!filled($value)) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return is_scalar($value) ? (string) $value : null;
    }

    private function productName(Model $product, array $config): string
    {
        return (string) ($product->{$config['name_column']} ?? 'Product');
    }

    private function productTypeLabel(Model $product, array $config): ?string
    {
        if ($config['key'] === 'motorcycle') {
            return filled($product->product_type)
                ? str((string) $product->product_type)->replace('_', ' ')->title()->toString()
                : null;
        }

        return $product->productType?->name ?? null;
    }

    private function variantLabel(Model $product, array $config): ?string
    {
        $parts = match ($config['key']) {
            'fashion' => [$product->size_label, $product->color],
            'home-needs' => [$product->unit_label, $product->color],
            default => [$this->productTypeLabel($product, $config)],
        };

        $label = collect($parts)
            ->filter(fn ($part) => filled($part))
            ->implode(' / ');

        return $label !== '' ? $label : null;
    }

    private function warrantyLabel(Model $product): ?string
    {
        $label = collect([
            $product->warrantyOption?->name,
            $product->warranty_period,
            $product->warranty ?? null,
        ])
            ->filter(fn ($part) => filled($part))
            ->implode(' - ');

        return $label !== '' ? $label : null;
    }

    private function brand(Model $product, array $config): ?Model
    {
        $relation = $config['brand_relation'];

        return $relation ? $product->{$relation} : null;
    }

    private function routeIdentifier(Model $product): int|string
    {
        return filled($product->slug) ? $product->slug : $product->id;
    }

    private function canonicalRedirectResponse(string $identifier, Model $product, string $routeName): ?RedirectResponse
    {
        if (!ctype_digit($identifier) || !filled($product->slug)) {
            return null;
        }

        return redirect()->route($routeName, [
            'product' => $product->slug,
        ], 301);
    }

    private function reviewPayload(MotorcycleProductReview $review): array
    {
        return [
            'id' => $review->id,
            'rating' => $review->rating !== null ? (int) $review->rating : null,
            'customer_name' => $review->customer_name,
            'short_description' => $review->short_description,
            'long_description' => $review->long_description,
            'image_urls' => $review->image_urls ?? [],
            'created_at' => $review->created_at?->format('d/m/Y'),
            'created_at_iso' => $review->created_at?->toIso8601String(),
        ];
    }

    private function brandLogoUrl(?Model $brand): ?string
    {
        if (!$brand) {
            return null;
        }

        $directUrl = data_get($brand, 'logo_url')
            ?? data_get($brand, 'image_url')
            ?? data_get($brand, 'icon_url');

        if (filled($directUrl)) {
            return (string) $directUrl;
        }

        $path = data_get($brand, 'logo_path')
            ?? data_get($brand, 'image_path')
            ?? data_get($brand, 'icon_path');

        if (!filled($path)) {
            return null;
        }

        $path = (string) $path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function sectionConfig(string $section): array
    {
        return match ($section) {
            'motorcycle' => [
                'key' => 'motorcycle',
                'card_prefix' => 'motorcycle',
                'title' => 'Motorcycle Products',
                'model' => MotorcycleProduct::class,
                'relations' => [
                    'category:id,name',
                    'helmetBrand:id,name,logo_path,status',
                    'compatibleHelmetBrand:id,name',
                    'compatibleBikeBrand:id,name',
                    'compatibleBikeModel:id,name',
                    'warrantyOption:id,name',
                ],
                'reviews' => true,
                'name_column' => 'name',
                'price_column' => 'regular_price',
                'long_description_column' => 'full_description',
                'brand_relation' => 'helmetBrand',
                'brand_name_column' => null,
                'index_route' => 'frontend.motorcycle-products.index',
                'show_route' => 'frontend.motorcycle-products.show',
                'data_route' => 'frontend.motorcycle-products.show.data',
                'spec_columns' => [
                    ['label' => 'Product Type', 'column' => 'product_type'],
                    ['label' => 'Category', 'column' => 'category.name'],
                    ['label' => 'Helmet Brand', 'column' => 'helmetBrand.name'],
                    ['label' => 'Compatible Helmet Brand', 'column' => 'compatibleHelmetBrand.name'],
                    ['label' => 'Compatible Bike Brand', 'column' => 'compatibleBikeBrand.name'],
                    ['label' => 'Compatible Bike Model', 'column' => 'compatibleBikeModel.name'],
                    ['label' => 'SKU', 'column' => 'sku'],
                ],
            ],
            'fashion' => [
                'key' => 'fashion',
                'card_prefix' => 'fashion',
                'title' => 'Fashion & Accessories',
                'model' => FashionProduct::class,
                'relations' => [
                    'category:id,name',
                    'brand:id,name,logo_path,status',
                    'productType:id,name',
                    'warrantyOption:id,name',
                ],
                'reviews' => false,
                'name_column' => 'name',
                'price_column' => 'price',
                'long_description_column' => 'full_description',
                'brand_relation' => 'brand',
                'brand_name_column' => 'brand_name',
                'index_route' => 'frontend.fashion.index',
                'show_route' => 'frontend.fashion.show',
                'data_route' => 'frontend.fashion.show.data',
                'spec_columns' => [
                    ['label' => 'Product Type', 'column' => 'productType.name'],
                    ['label' => 'Category', 'column' => 'category.name'],
                    ['label' => 'Brand', 'column' => 'brand.name'],
                    ['label' => 'Brand Name', 'column' => 'brand_name'],
                    ['label' => 'Gender', 'column' => 'target_gender'],
                    ['label' => 'Size', 'column' => 'size_label'],
                    ['label' => 'Color', 'column' => 'color'],
                    ['label' => 'Material', 'column' => 'material'],
                    ['label' => 'Style', 'column' => 'style'],
                    ['label' => 'Fit', 'column' => 'fit'],
                    ['label' => 'Lens Type', 'column' => 'lens_type'],
                    ['label' => 'Frame Material', 'column' => 'frame_material'],
                    ['label' => 'Bag Size', 'column' => 'bag_size'],
                    ['label' => 'Closure Type', 'column' => 'closure_type'],
                    ['label' => 'Strap Type', 'column' => 'strap_type'],
                    ['label' => 'Dimensions', 'column' => 'dimensions'],
                    ['label' => 'Care Instructions', 'column' => 'care_instructions'],
                    ['label' => 'SKU', 'column' => 'sku'],
                ],
            ],
            'home-needs' => [
                'key' => 'home-needs',
                'card_prefix' => 'home-need',
                'title' => 'Home Needs',
                'model' => HomeNeedProduct::class,
                'relations' => [
                    'category:id,name',
                    'brand:id,name,logo_path,status',
                    'warrantyOption:id,name',
                ],
                'reviews' => false,
                'name_column' => 'name',
                'price_column' => 'price',
                'long_description_column' => 'full_description',
                'brand_relation' => 'brand',
                'brand_name_column' => 'brand_name',
                'index_route' => 'frontend.home-needs.index',
                'show_route' => 'frontend.home-needs.show',
                'data_route' => 'frontend.home-needs.show.data',
                'spec_columns' => [
                    ['label' => 'Category', 'column' => 'category.name'],
                    ['label' => 'Brand', 'column' => 'brand.name'],
                    ['label' => 'Brand Name', 'column' => 'brand_name'],
                    ['label' => 'Unit', 'column' => 'unit_label'],
                    ['label' => 'Material', 'column' => 'material'],
                    ['label' => 'Color', 'column' => 'color'],
                    ['label' => 'SKU', 'column' => 'sku'],
                ],
            ],
            default => abort(404),
        };
    }
}
