<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CosmeticProduct;
use App\Models\CosmeticProductReview;
use App\Models\CosmeticSizeVolume;
use App\Models\Invoice;
use App\Models\KokoPaySetting;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\ShoeProductReview;
use App\Support\KokoPay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CosmeticProductViewController extends Controller
{
    public function index(string $product): Response|RedirectResponse
    {
        $cosmetic = $this->findProduct($product);

        abort_if(!$cosmetic, 404);

        if ($redirect = $this->canonicalRedirectResponse($product, $cosmetic)) {
            return $redirect;
        }

        return Inertia::render('Frontend/cosmetic_productview/index', [
            'productKey' => $product,
            'shell' => [
                'name' => $cosmetic->name,
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Cosmetic Products', 'href' => '/cosmetics'],
                    ['label' => $cosmetic->name, 'href' => null],
                ],
            ],
        ]);
    }

    public function data(string $product): JsonResponse
    {
        $cosmetic = $this->findProduct($product);

        abort_if(!$cosmetic, 404);

        $cosmetic->loadCount('reviews')->loadAvg('reviews', 'rating');

        $cosmetic->load([
            'brand',
            'category',
            'productType',
            'countryOfOrigin',
            'variants.sizeVolume',
        ]);

        $fallbackStock = $cosmetic->stock !== null ? (int) $cosmetic->stock : 0;

        $volumeIds = collect($cosmetic->size_volume_ids ?? [])
            ->merge($cosmetic->variants->pluck('cosmetic_size_volume_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $volumes = CosmeticSizeVolume::query()
            ->whereIn('id', $volumeIds)
            ->orderBy('size')
            ->get()
            ->map(fn (CosmeticSizeVolume $volume) => [
                'id' => (int) $volume->id,
                'label' => $volume->display,
            ])
            ->values();

        $variantRows = $cosmetic->variants
            ->map(function ($variant) use ($cosmetic, $fallbackStock) {
                $price = $variant->price !== null ? (float) $variant->price : (float) ($cosmetic->price ?? 0);
                $discount = $this->resolveDiscount($price, $cosmetic->discount_type, $cosmetic->discount_value);

                $resolvedStock = $variant->stock_count !== null
                    ? (int) $variant->stock_count
                    : $fallbackStock;

                $variantStatus = $variant->status ?: 'active';
                $inStock = $variantStatus !== 'inactive' && $resolvedStock > 0;

                return [
                    'id' => $variant->id,
                    'volume_id' => $variant->cosmetic_size_volume_id !== null ? (int) $variant->cosmetic_size_volume_id : null,
                    'volume_label' => $variant->sizeVolume?->display,
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
            $price = (float) ($cosmetic->price ?? 0);
            $discount = $this->resolveDiscount($price, $cosmetic->discount_type, $cosmetic->discount_value);

            $resolvedStock = $fallbackStock;
            $inStock = $resolvedStock > 0;

            $variantRows = collect([[
                'id' => 'default',
                'volume_id' => $volumes->first()['id'] ?? null,
                'volume_label' => $volumes->first()['label'] ?? null,
                'sku' => $cosmetic->batch_number,
                'price_lkr' => $price,
                'old_price_lkr' => $discount['old_price'],
                'final_price_lkr' => $discount['current_price'],
                'stock_count' => $resolvedStock,
                'in_stock' => $inStock,
                'status' => $inStock ? 'active' : 'inactive',
                'discount_label' => $discount['label'],
            ]]);
        }

        $displayVariant = $variantRows
            ->first(fn ($row) => ($row['in_stock'] ?? false) === true)
            ?: $variantRows->first(fn ($row) => ($row['status'] ?? 'active') !== 'inactive')
            ?: $variantRows->first();

        $basePrice = (float) ($displayVariant['price_lkr'] ?? ($cosmetic->price ?? 0));
        $discount = $this->resolveDiscount($basePrice, $cosmetic->discount_type, $cosmetic->discount_value);

        $galleryUrls = collect([$cosmetic->main_image_url, ...($cosmetic->gallery_urls ?? [])])
            ->filter()
            ->unique()
            ->take(6)
            ->values();

        $gallery = $galleryUrls
            ->map(fn ($url, $index) => [
                'id' => 'gallery-' . $index,
                'src' => $url,
            ])
            ->values();

        $mainImage = $cosmetic->main_image_url ?: ($gallery->first()['src'] ?? null);

        $resolvedStock = (int) ($displayVariant['stock_count'] ?? $fallbackStock);
        $inStock = (bool) ($displayVariant['in_stock'] ?? ($resolvedStock > 0));

        $defaultVolumeId = $displayVariant['volume_id'] ?? ($volumes->first()['id'] ?? null);

        $kokoPayPercentage = KokoPaySetting::currentPercentage();

        return response()->json([
            'product' => [
                'id' => $cosmetic->id,
                'name' => $cosmetic->name,
                'slug' => $cosmetic->slug,
                'sku' => $displayVariant['sku'] ?? $cosmetic->batch_number,
                'batch_number' => $cosmetic->batch_number,
                'manufacture_date' => optional($cosmetic->manufacture_date)->format('Y-m-d'),
                'expiry_date' => optional($cosmetic->expiry_date)->format('Y-m-d'),
                'short_description' => $cosmetic->short_description,
                'long_description' => $cosmetic->long_description,
                'brand' => [
                    'id' => $cosmetic->brand?->id,
                    'name' => $cosmetic->brand?->name,
                    'logo_url' => $cosmetic->brand?->logo_url,
                ],
                'category' => [
                    'id' => $cosmetic->category?->id,
                    'name' => $cosmetic->category?->name,
                ],
                'product_type' => [
                    'id' => $cosmetic->productType?->id,
                    'name' => $cosmetic->productType?->name,
                ],
                'country' => [
                    'id' => $cosmetic->countryOfOrigin?->id,
                    'name' => $cosmetic->countryOfOrigin?->name,
                    'code' => $cosmetic->countryOfOrigin?->code,
                    'flag_image_url' => $cosmetic->countryOfOrigin?->flag_image_url,
                ],
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Cosmetic Products', 'href' => '/cosmetics'],
                    ['label' => $cosmetic->name, 'href' => null],
                ],
                'main_image' => $mainImage,
                'gallery' => $gallery,
                'volumes' => $volumes,
                'variants' => $variantRows,
                'base_price' => $basePrice,
                'old_price' => $discount['old_price'],
                'current_price' => $discount['current_price'],
                'has_discount' => $discount['has_discount'],
                'discount_label' => $discount['label'],
                'default_volume_id' => $defaultVolumeId,
                'stock_count' => $resolvedStock,
                'in_stock' => $inStock,
                'reviews_count' => (int) ($cosmetic->reviews_count ?? 0),
                'reviews_avg_rating' => $cosmetic->reviews_avg_rating !== null ? (float) $cosmetic->reviews_avg_rating : null,
            ],
            'related_products' => $this->relatedProducts($cosmetic, $kokoPayPercentage),
        ]);
    }

    public function reviews(Request $request, string $product): JsonResponse
    {
        $cosmetic = $this->findProduct($product);

        abort_if(!$cosmetic, 404);

        $cosmetic->loadCount('reviews')->loadAvg('reviews', 'rating');

        $sort = (string) $request->query('sort', 'recent');
        $sort = in_array($sort, ['recent', 'highest', 'lowest'], true) ? $sort : 'recent';

        $limit = (int) $request->query('limit', 4);
        $limit = max(1, min($limit, 4));

        $offset = (int) $request->query('offset', 0);
        $offset = max(0, $offset);

        $reviewsQuery = $cosmetic->reviews();

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
            ->map(function (CosmeticProductReview $review) {
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
        $total = (int) ($cosmetic->reviews_count ?? 0);

        return response()->json([
            'summary' => [
                'reviews_count' => (int) ($cosmetic->reviews_count ?? 0),
                'avg_rating' => $cosmetic->reviews_avg_rating !== null ? (float) $cosmetic->reviews_avg_rating : null,
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
        $cosmetic = $this->findProduct($product);

        abort_if(!$cosmetic, 404);

        $validated = $request->validate([
            'invoice_no' => ['required', 'string', 'regex:/^INV-\\d+$/', 'max:50'],
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

        $review = new CosmeticProductReview();
        $review->product_id = $cosmetic->id;
        $review->invoice_no = $invoiceNo;
        $review->rating = (int) $validated['rating'];
        $review->customer_name = $customerName;
        $review->customer_email = null;
        $review->short_description = null;
        $review->long_description = $longDescription;
        $review->image_paths = $imagePaths;
        $review->save();

        $cosmetic->loadCount('reviews')->loadAvg('reviews', 'rating');

        return response()->json([
            'message' => 'Review submitted successfully.',
            'summary' => [
                'reviews_count' => (int) ($cosmetic->reviews_count ?? 0),
                'avg_rating' => $cosmetic->reviews_avg_rating !== null ? (float) $cosmetic->reviews_avg_rating : null,
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

    private function relatedProducts(CosmeticProduct $product, float $kokoPayPercentage)
    {
        if (!$product->category_id) {
            return collect([]);
        }

        return CosmeticProduct::query()
            ->with(['brand', 'category', 'countryOfOrigin'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'active')
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->oldest('id')
            ->take(4)
            ->get()
            ->map(fn (CosmeticProduct $item) => $this->mapProductCard($item, $kokoPayPercentage))
            ->values();
    }

    private function mapProductCard(CosmeticProduct $product, float $kokoPayPercentage): array
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
            'koko_pay_percentage' => KokoPay::percentage($kokoPayPercentage),
            'koko_installment_price' => KokoPay::installmentAmount($discount['current_price'], $kokoPayPercentage),
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

    private function resolveDiscount(?float $price, ?string $discountType, $discountValue): array
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

    private function findProduct(string $key): ?CosmeticProduct
    {
        return CosmeticProduct::query()
            ->where('status', 'active')
            ->where(function ($query) use ($key) {
                if (is_numeric($key)) {
                    $query->whereKey((int) $key);
                }

                $query->orWhere('slug', $key);
            })
            ->first();
    }

    private function canonicalRedirectResponse(string $identifier, CosmeticProduct $product): ?RedirectResponse
    {
        if (!ctype_digit($identifier) || !filled($product->slug)) {
            return null;
        }

        return redirect()->route('frontend.cosmetic-products.show', [
            'product' => $product->slug,
        ], 301);
    }
}
