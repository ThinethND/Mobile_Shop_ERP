<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ColorOption;
use App\Models\CosmeticProductReview;
use App\Models\Invoice;
use App\Models\KokoPaySetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ShoeProductReview;
use App\Models\StorageOption;
use App\Support\KokoPay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TechProductViewController extends Controller
{
    public function index(string $product): Response|RedirectResponse
    {
        $productModel = $this->findProduct($product);

        abort_if(!$productModel || $productModel->status !== 'active', 404);

        if ($redirect = $this->canonicalRedirect($product, $productModel)) {
            return $redirect;
        }

        return Inertia::render('Frontend/techproduct_view/index', [
            'productKey' => $this->routeIdentifier($productModel),
            'shell' => [
                'name' => $productModel->model,
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Tech Products', 'href' => '/tech-products'],
                    ['label' => $productModel->model, 'href' => null],
                ],
            ],
        ]);
    }

    public function data(string $product): JsonResponse
    {
        $productModel = $this->findProduct($product);

        abort_if(!$productModel || $productModel->status !== 'active', 404);

        $productModel->load([
            'category',
            'brand',
            'warrantyOption',
            'colors',
            'variants',
        ]);

        $productModel->loadCount('reviews')->loadAvg('reviews', 'rating');

        $fallbackProductStock = $productModel->stock_count !== null
            ? (int) $productModel->stock_count
            : ((bool) $productModel->in_stock ? 1 : 0);

        $storageIds = collect($productModel->storage_option_ids ?: [])
            ->merge($productModel->storage_option_id ? [$productModel->storage_option_id] : [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $colorIds = collect($productModel->color_ids ?: [])
            ->merge($productModel->colors->pluck('id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $storageOptions = StorageOption::query()
            ->whereIn('id', $storageIds)
            ->orderBy('value')
            ->get()
            ->map(fn ($item) => [
                'id' => (int) $item->id,
                'label' => trim($item->value . ' ' . $item->unit),
            ])
            ->values();

        $colorOptions = ColorOption::query()
            ->whereIn('id', $colorIds)
            ->orderBy('name')
            ->get()
            ->map(function ($item) use ($productModel) {
                $relatedColor = $productModel->colors->firstWhere('id', $item->id);

                return [
                    'id' => (int) $item->id,
                    'name' => $item->name,
                    'color_code' => $item->color_code,
                    'image_url' => $relatedColor?->image_url ?? $item->image_url,
                ];
            })
            ->values();

        $variantRows = $productModel->variants
            ->map(function ($variant) use ($productModel, $fallbackProductStock) {
                $price = (float) ($variant->price_lkr ?: $productModel->price_lkr ?: 0);
                $discount = $this->resolveDiscount($price, $productModel->discount_type, $productModel->discount_value);

                $resolvedStock = $variant->stock_count !== null
                    ? (int) $variant->stock_count
                    : $fallbackProductStock;

                $variantStatus = $variant->status ?: 'active';
                $inStock = $variantStatus !== 'inactive' && $resolvedStock > 0;

                return [
                    'id' => $variant->id,
                    'color_option_id' => filled($variant->color_option_id) ? (int) $variant->color_option_id : null,
                    'storage_option_id' => filled($variant->storage_option_id) ? (int) $variant->storage_option_id : null,
                    'sku' => $variant->sku,
                    'price_lkr' => $price,
                    'old_price_lkr' => $discount['old_price'],
                    'final_price_lkr' => $discount['current_price'],
                    'stock_count' => $resolvedStock,
                    'in_stock' => $inStock,
                    'status' => $variantStatus,
                    'discount_label' => $discount['label'],
                ];
            })
            ->values();

        if ($variantRows->isEmpty()) {
            $discount = $this->resolveDiscount(
                (float) ($productModel->price_lkr ?: 0),
                $productModel->discount_type,
                $productModel->discount_value
            );

            $variantRows = collect([[
                'id' => 'default',
                'color_option_id' => $colorOptions->first()['id'] ?? null,
                'storage_option_id' => $storageOptions->first()['id'] ?? null,
                'sku' => $productModel->sku,
                'price_lkr' => (float) ($productModel->price_lkr ?: 0),
                'old_price_lkr' => $discount['old_price'],
                'final_price_lkr' => $discount['current_price'],
                'stock_count' => $fallbackProductStock,
                'in_stock' => ((bool) $productModel->in_stock) && $fallbackProductStock > 0,
                'status' => 'active',
                'discount_label' => $discount['label'],
            ]]);
        }

        $displayVariant = $variantRows->first(fn ($variant) => ($variant['in_stock'] ?? false) === true)
            ?: $variantRows->first(fn ($variant) => ($variant['status'] ?? 'active') !== 'inactive')
            ?: $variantRows->first();

        $specifications = collect([
            ['label' => 'Operating System', 'value' => $productModel->os],
            ['label' => 'Display Size', 'value' => $productModel->display_size],
            ['label' => 'Display Type', 'value' => $productModel->display_type],
            ['label' => 'Resolution', 'value' => $productModel->resolution],
            ['label' => 'Rear Camera', 'value' => $productModel->rear_camera],
            ['label' => 'Front Camera', 'value' => $productModel->front_camera],
            ['label' => 'Connectivity', 'value' => $productModel->connectivity],
            ['label' => 'Battery', 'value' => $productModel->battery_mah ? $productModel->battery_mah . ' mAh' : null],
            ['label' => 'Device Status', 'value' => ucfirst((string) $productModel->device_status)],
            ['label' => 'Warranty', 'value' => $this->warrantyLabel($productModel)],
            ['label' => 'SKU', 'value' => $productModel->sku],
        ])
            ->filter(fn ($row) => filled($row['value']))
            ->values();

        $gallery = collect($productModel->gallery_urls ?? [])
            ->filter()
            ->unique()
            ->take(4)
            ->values()
            ->map(fn ($url, $index) => [
                'id' => 'gallery-' . $index,
                'src' => $url,
            ])
            ->values();

        $mainImage = $productModel->main_image_url ?: ($gallery->first()['src'] ?? null);

        $kokoPayPercentage = KokoPaySetting::currentPercentage();
        $relatedProducts = $this->relatedProducts($productModel, $kokoPayPercentage);

        return response()->json([
            'product' => [
                'id' => $productModel->id,
                'slug' => $productModel->slug,
                'name' => $productModel->model,
                'sku' => $productModel->sku,
                'short_description' => $productModel->short_description,
                'long_description' => $productModel->long_description,
                'brand' => [
                    'id' => $productModel->brand?->id,
                    'name' => $productModel->brand?->name,
                    'logo_url' => $this->brandLogoUrl($productModel->brand),
                ],
                'category' => [
                    'id' => $productModel->category?->id,
                    'name' => $productModel->category?->name,
                ],
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Tech Products', 'href' => '/tech-products'],
                    ['label' => $productModel->model, 'href' => null],
                ],
                'main_image' => $mainImage,
                'gallery' => $gallery,
                'colors' => $colorOptions,
                'storage_options' => $storageOptions,
                'variants' => $variantRows,
                'warranty_label' => $this->warrantyLabel($productModel),
                'base_price' => (float) ($productModel->price_lkr ?: 0),
                'old_price' => $displayVariant['old_price_lkr'] ?? null,
                'current_price' => $displayVariant['final_price_lkr'] ?? (float) ($productModel->price_lkr ?: 0),
                'koko_pay_percentage' => KokoPay::percentage($kokoPayPercentage),
                'koko_installment_price' => KokoPay::installmentAmount(
                    (float) ($displayVariant['final_price_lkr'] ?? $productModel->price_lkr ?? 0),
                    $kokoPayPercentage
                ),
                'has_discount' => !empty($displayVariant['old_price_lkr'])
                    && (float) $displayVariant['old_price_lkr'] > (float) ($displayVariant['final_price_lkr'] ?? 0),
                'discount_label' => $displayVariant['discount_label'] ?? null,
                'specifications' => $specifications,
                'default_color_id' => $displayVariant['color_option_id'] ?? ($colorOptions->first()['id'] ?? null),
                'default_storage_id' => $displayVariant['storage_option_id'] ?? ($storageOptions->first()['id'] ?? null),
                'stock_count' => $displayVariant['stock_count'] ?? $fallbackProductStock,
                'in_stock' => $displayVariant['in_stock'] ?? (((bool) $productModel->in_stock) && $fallbackProductStock > 0),
                'reviews_count' => (int) ($productModel->reviews_count ?? 0),
                'reviews_avg_rating' => $productModel->reviews_avg_rating !== null ? (float) $productModel->reviews_avg_rating : null,
            ],
            'related_products' => $relatedProducts,
        ])->header('Cache-Control', 'private, max-age=90');
    }

    public function reviews(Request $request, string $product): JsonResponse
    {
        $productModel = $this->findProduct($product);

        abort_if(!$productModel || $productModel->status !== 'active', 404);

        $productModel->loadCount('reviews')->loadAvg('reviews', 'rating');

        $sort = (string) $request->query('sort', 'recent');
        $sort = in_array($sort, ['recent', 'highest', 'lowest'], true) ? $sort : 'recent';

        $limit = (int) $request->query('limit', 4);
        $limit = max(1, min($limit, 4));

        $offset = (int) $request->query('offset', 0);
        $offset = max(0, $offset);

        $reviewsQuery = $productModel->reviews();

        if ($sort === 'highest') {
            $reviewsQuery
                ->orderByRaw('rating is null')
                ->orderByDesc('rating')
                ->orderByDesc('id');
        } elseif ($sort === 'lowest') {
            $reviewsQuery
                ->orderByRaw('rating is null')
                ->orderBy('rating')
                ->orderByDesc('id');
        } else {
            $reviewsQuery->orderByDesc('id');
        }

        $reviews = $reviewsQuery
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($review) {
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
            })
            ->values();

        $returned = $reviews->count();
        $total = (int) ($productModel->reviews_count ?? 0);

        return response()->json([
            'summary' => [
                'reviews_count' => $total,
                'avg_rating' => $productModel->reviews_avg_rating !== null ? (float) $productModel->reviews_avg_rating : null,
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

    public function storeReview(Request $request, string $product): JsonResponse
    {
        $productModel = $this->findProduct($product);

        abort_if(!$productModel || $productModel->status !== 'active', 404);

        $validated = $request->validate([
            'invoice_no' => ['required', 'string', 'regex:/^INV-\d+$/', 'max:50'],
            'anonymous' => ['nullable', 'boolean'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'long_description' => ['nullable', 'string', 'max:5000'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $invoiceNo = $validated['invoice_no'];
        $anonymous = (bool) ($validated['anonymous'] ?? false);
        $customerName = $anonymous ? null : ($validated['customer_name'] ?? null);
        $customerName = filled($customerName) ? $customerName : null;
        $longDescription = $validated['long_description'] ?? null;
        $longDescription = filled($longDescription) ? $longDescription : null;

        $invoiceExists = Invoice::query()->where('invoice_no', $invoiceNo)->exists()
            || Order::query()->where('order_number', $invoiceNo)->exists();

        if (!$invoiceExists) {
            throw ValidationException::withMessages([
                'invoice_no' => 'The invoice ID you entered was not found in our system. Please check and try again.',
            ]);
        }

        $invoiceAlreadyReviewed = ProductReview::query()->where('invoice_no', $invoiceNo)->exists()
            || ShoeProductReview::query()->where('invoice_no', $invoiceNo)->exists()
            || CosmeticProductReview::query()->where('invoice_no', $invoiceNo)->exists();

        if ($invoiceAlreadyReviewed) {
            throw ValidationException::withMessages([
                'invoice_no' => 'A review has already been submitted using this invoice ID.',
            ]);
        }

        $imagePaths = $request->hasFile('images')
            ? collect($request->file('images'))
                ->map(fn ($file) => $file->store('product-reviews', 'public'))
                ->values()
                ->all()
            : [];

        $review = new ProductReview();
        $review->product_id = $productModel->id;
        $review->invoice_no = $invoiceNo;
        $review->rating = (int) $validated['rating'];
        $review->customer_name = $customerName;
        $review->customer_email = null;
        $review->short_description = null;
        $review->long_description = $longDescription;
        $review->image_paths = $imagePaths;
        $review->save();

        $productModel->loadCount('reviews')->loadAvg('reviews', 'rating');

        return response()->json([
            'message' => 'Review submitted successfully.',
            'summary' => [
                'reviews_count' => (int) ($productModel->reviews_count ?? 0),
                'avg_rating' => $productModel->reviews_avg_rating !== null ? (float) $productModel->reviews_avg_rating : null,
            ],
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating !== null ? (int) $review->rating : null,
                'customer_name' => $review->customer_name,
                'short_description' => $review->short_description,
                'long_description' => $review->long_description,
                'image_urls' => $review->image_urls ?? [],
                'created_at' => $review->created_at?->format('d/m/Y'),
                'created_at_iso' => $review->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    private function relatedProducts(Product $product, float $kokoPayPercentage)
    {
        if (!$product->category_id) {
            return collect([]);
        }

        return Product::query()
            ->with(['category', 'brand', 'colors', 'variants'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->oldest('id')
            ->take(4)
            ->get()
            ->map(fn (Product $item) => $this->mapProductCard($item, $kokoPayPercentage))
            ->values();
    }

    private function mapProductCard(Product $product, float $kokoPayPercentage): array
    {
        $product->loadMissing(['category', 'brand', 'colors', 'variants']);

        $fallbackProductStock = $product->stock_count !== null
            ? (int) $product->stock_count
            : ((bool) $product->in_stock ? 1 : 0);

        $displayVariant = $product->variants
            ->map(function ($variant) use ($product, $fallbackProductStock) {
                $price = (float) ($variant->price_lkr ?: $product->price_lkr ?: 0);
                $resolvedStock = $variant->stock_count !== null
                    ? (int) $variant->stock_count
                    : $fallbackProductStock;

                $variantStatus = $variant->status ?: 'active';
                $inStock = $variantStatus !== 'inactive' && $resolvedStock > 0;

                return [
                    'id' => $variant->id,
                    'price_lkr' => $price,
                    'stock_count' => $resolvedStock,
                    'in_stock' => $inStock,
                    'status' => $variantStatus,
                ];
            })
            ->first(fn ($variant) => ($variant['in_stock'] ?? false) === true)
            ?: $product->variants
                ->map(function ($variant) use ($product, $fallbackProductStock) {
                    $price = (float) ($variant->price_lkr ?: $product->price_lkr ?: 0);
                    $resolvedStock = $variant->stock_count !== null
                        ? (int) $variant->stock_count
                        : $fallbackProductStock;

                    $variantStatus = $variant->status ?: 'active';
                    $inStock = $variantStatus !== 'inactive' && $resolvedStock > 0;

                    return [
                        'id' => $variant->id,
                        'price_lkr' => $price,
                        'stock_count' => $resolvedStock,
                        'in_stock' => $inStock,
                        'status' => $variantStatus,
                    ];
                })
                ->first(fn ($variant) => ($variant['status'] ?? 'active') !== 'inactive');

        $basePrice = (float) ($displayVariant['price_lkr'] ?? $product->price_lkr ?? 0);
        $discount = $this->resolveDiscount($basePrice, $product->discount_type, $product->discount_value);

        $galleryUrls = collect($product->gallery_urls ?? [])
            ->filter()
            ->values();

        $thumbnailUrl = $product->main_image_url ?: $galleryUrls->first();
        $hoverImageUrl = data_get($product, 'hover_image_url')
            ?: $galleryUrls->first(fn ($url) => $url !== $thumbnailUrl)
            ?: $thumbnailUrl;

        $resolvedStock = (int) ($displayVariant['stock_count'] ?? $fallbackProductStock);
        $inStock = (bool) ($displayVariant['in_stock'] ?? (((bool) $product->in_stock) && $resolvedStock > 0));

        return [
            'id' => $product->id,
            'name' => $product->model,
            'slug' => $product->slug,
            'category_name' => $product->category?->name,
            'brand_name' => $product->brand?->name,
            'thumbnail_url' => $thumbnailUrl,
            'hover_image_url' => $hoverImageUrl,
            'regular_price' => $discount['old_price'],
            'display_price' => $discount['current_price'],
            'koko_pay_percentage' => KokoPay::percentage($kokoPayPercentage),
            'koko_installment_price' => KokoPay::installmentAmount($discount['current_price'], $kokoPayPercentage),
            'has_discount' => $discount['has_discount'],
            'discount_label' => $discount['label'],
            'is_sold_out' => !$inStock || $resolvedStock <= 0,
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'colors' => $product->colors
                ->sortBy('name')
                ->map(fn ($color) => [
                    'id' => $color->id,
                    'name' => $color->name,
                    'color_code' => $color->color_code,
                    'image_url' => $color->image_url,
                ])
                ->values(),
            'url' => route('frontend.tech-products.show', ['product' => $this->routeIdentifier($product)]),
        ];
    }

    private function findProduct(string $identifier): ?Product
    {
        return Product::query()
            ->where('status', 'active')
            ->where(function ($query) use ($identifier) {
                if (ctype_digit($identifier)) {
                    $query->whereKey((int) $identifier);
                }

                $query->orWhere('slug', $identifier);
            })
            ->first();
    }

    private function canonicalRedirect(string $identifier, Product $product): ?RedirectResponse
    {
        if (!ctype_digit($identifier) || !filled($product->slug)) {
            return null;
        }

        return redirect()->route('frontend.tech-products.show', [
            'product' => $product->slug,
        ], 301);
    }

    private function routeIdentifier(Product $product): string|int
    {
        return $product->routeIdentifier();
    }

    private function resolveDiscount(float $price, ?string $discountType, $discountValue): array
    {
        $price = max(0, round($price, 2));
        $discountValue = is_numeric($discountValue) ? (float) $discountValue : 0.0;

        if ($discountValue <= 0 || !in_array($discountType, ['percent', 'price'], true)) {
            return [
                'old_price' => null,
                'current_price' => $price,
                'has_discount' => false,
                'label' => null,
            ];
        }

        if ($discountType === 'percent') {
            $currentPrice = max(0, round($price - (($price * $discountValue) / 100), 2));
            $label = rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.') . '% OFF';
        } else {
            $currentPrice = max(0, round($price - $discountValue, 2));
            $label = 'Save Rs ' . number_format($discountValue, 2);
        }

        return [
            'old_price' => $currentPrice < $price ? $price : null,
            'current_price' => $currentPrice,
            'has_discount' => $currentPrice < $price,
            'label' => $currentPrice < $price ? $label : null,
        ];
    }

    private function warrantyLabel(Product $product): ?string
    {
        $segments = array_filter([
            $product->warrantyOption?->name,
            $product->warranty_period,
        ]);

        return empty($segments) ? null : implode(' - ', $segments);
    }

    private function brandLogoUrl($brand): ?string
    {
        if (!$brand) {
            return null;
        }

        return data_get($brand, 'logo_url')
            ?? data_get($brand, 'image_url')
            ?? data_get($brand, 'icon_url');
    }
}
