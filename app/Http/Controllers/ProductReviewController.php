<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductReviewController extends Controller
{
    public function products()
    {
        return Inertia::render('ProductReviews/ReviewProductList');
    }

    public function productsData(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('model', 'like', "%{$searchValue}%")
                    ->orWhere('sku', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'model',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (Product $product) {
            $imgUrl = $product->main_image_url;

            $productHtml = $imgUrl
                ? '<div class="flex items-center gap-3">
                        <img src="' . e($imgUrl) . '" alt="' . e($product->model) . '" class="h-12 w-12 rounded-xl border border-neutral-200 object-cover" />
                        <div>
                            <div class="font-medium text-neutral-800">' . e($product->model) . '</div>
                            <div class="text-xs text-neutral-500">' . e($product->sku ?? '-') . '</div>
                        </div>
                   </div>'
                : '<div>
                        <div class="font-medium text-neutral-800">' . e($product->model) . '</div>
                        <div class="text-xs text-neutral-500">' . e($product->sku ?? '-') . '</div>
                   </div>';

            $reviewsCount = (int) ($product->reviews_count ?? 0);
            $avg = $product->reviews_avg_rating;
            $avgDisplay = $avg !== null ? number_format((float) $avg, 1) : null;

            $reviewsHtml = $reviewsCount > 0 && $avgDisplay !== null
                ? '<div class="text-sm font-medium text-neutral-800">' . e($avgDisplay) . ' / 5</div>
                   <div class="text-xs text-neutral-500">' . e($reviewsCount) . ' review' . ($reviewsCount === 1 ? '' : 's') . '</div>'
                : '<div class="text-sm text-neutral-500">-</div>
                   <div class="text-xs text-neutral-400">0 reviews</div>';

            $payload = e(json_encode([
                'id' => $product->id,
                'name' => $product->model,
            ], JSON_UNESCAPED_UNICODE));

            $actions = '
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        data-action="add"
                        data-payload=\'' . $payload . '\'
                        class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100"
                    >
                        Add Review
                    </button>
                    <button
                        type="button"
                        data-action="view"
                        data-payload=\'' . $payload . '\'
                        class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                    >
                        View Reviews
                    </button>
                </div>
            ';

            return [
                'id' => $product->id,
                'product_info' => $productHtml,
                'reviews' => $reviewsHtml,
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function index(Product $product)
    {
        $product->loadCount('reviews')->loadAvg('reviews', 'rating');

        return Inertia::render('ProductReviews/index', [
            'product' => [
                'id' => $product->id,
                'name' => $product->model,
                'main_image_url' => $product->main_image_url,
                'reviews_count' => (int) ($product->reviews_count ?? 0),
                'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            ],
        ]);
    }

    public function reviewsData(Request $request, Product $product)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = ProductReview::query()
            ->where('product_id', $product->id);

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('customer_name', 'like', "%{$searchValue}%")
                    ->orWhere('customer_email', 'like', "%{$searchValue}%")
                    ->orWhere('short_description', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'rating',
            2 => 'customer_name',
            5 => 'created_at',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (ProductReview $review) {
            $ratingHtml = $review->rating !== null
                ? '<span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800">' . e((string) $review->rating) . ' / 5</span>'
                : '<span class="text-xs text-neutral-400">-</span>';

            $customerHtml = '
                <div>
                    <div class="font-medium text-neutral-800">' . e($review->customer_name ?: '-') . '</div>
                    <div class="text-xs text-neutral-500">' . e($review->customer_email ?: '-') . '</div>
                </div>
            ';

            $shortDesc = (string) ($review->short_description ?? '');
            $shortDesc = mb_strlen($shortDesc) > 120 ? (mb_substr($shortDesc, 0, 117) . '...') : $shortDesc;
            $descHtml = $shortDesc !== ''
                ? '<div class="text-sm text-neutral-700">' . e($shortDesc) . '</div>'
                : '<div class="text-sm text-neutral-400">-</div>';

            $images = $review->image_urls ?? [];
            $imagesHtml = empty($images)
                ? '<span class="text-sm text-neutral-400">-</span>'
                : '<div class="flex -space-x-2">'
                    . collect($images)->take(3)->map(function ($url) {
                        return '<img src="' . e($url) . '" class="h-8 w-8 rounded-full border border-white object-cover" />';
                    })->implode('')
                    . (count($images) > 3 ? '<span class="ml-2 text-xs text-neutral-500">+' . (count($images) - 3) . '</span>' : '')
                    . '</div>';

            $payload = e(json_encode([
                'id' => $review->id,
                'customer_name' => $review->customer_name,
            ], JSON_UNESCAPED_UNICODE));

            $actions = '
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        data-action="edit"
                        data-payload=\'' . $payload . '\'
                        class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100"
                    >
                        Edit
                    </button>
                    <button
                        type="button"
                        data-action="delete"
                        data-payload=\'' . $payload . '\'
                        class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                    >
                        Delete
                    </button>
                </div>
            ';

            return [
                'id' => $review->id,
                'rating' => $ratingHtml,
                'customer' => $customerHtml,
                'short_description' => $descHtml,
                'images' => $imagesHtml,
                'created_at' => optional($review->created_at)->format('Y-m-d H:i'),
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function create(Product $product)
    {
        return Inertia::render('ProductReviews/partials/CreateUpdate', [
            'mode' => 'create',
            'product' => [
                'id' => $product->id,
                'name' => $product->model,
            ],
            'review' => null,
        ]);
    }

    public function edit(Product $product, ProductReview $review)
    {
        $this->ensureReviewBelongsToProduct($product, $review);

        return Inertia::render('ProductReviews/partials/CreateUpdate', [
            'mode' => 'edit',
            'product' => [
                'id' => $product->id,
                'name' => $product->model,
            ],
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'customer_name' => $review->customer_name,
                'customer_email' => $review->customer_email,
                'short_description' => $review->short_description,
                'long_description' => $review->long_description,
                'image_urls' => $review->image_urls,
            ],
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $validated = $this->validateReview($request);

        $review = new ProductReview();
        $review->product_id = $product->id;
        $review->rating = $validated['rating'] ?? null;
        $review->customer_name = $validated['customer_name'] ?? null;
        $review->customer_email = $validated['customer_email'] ?? null;
        $review->short_description = $validated['short_description'] ?? null;
        $review->long_description = $validated['long_description'] ?? null;
        $review->image_paths = $this->storeImages($request);
        $review->save();

        return redirect()
            ->route('product-reviews.reviews.index', $product->id)
            ->with('success', 'Review created.');
    }

    public function update(Request $request, Product $product, ProductReview $review)
    {
        $this->ensureReviewBelongsToProduct($product, $review);

        $validated = $this->validateReview($request);

        $review->rating = $validated['rating'] ?? null;
        $review->customer_name = $validated['customer_name'] ?? null;
        $review->customer_email = $validated['customer_email'] ?? null;
        $review->short_description = $validated['short_description'] ?? null;
        $review->long_description = $validated['long_description'] ?? null;

        if ($request->hasFile('images')) {
            $existing = is_array($review->image_paths) ? $review->image_paths : [];
            $review->image_paths = array_values(array_filter(array_merge($existing, $this->storeImages($request))));
        }

        $review->save();

        return redirect()
            ->route('product-reviews.reviews.index', $product->id)
            ->with('success', 'Review updated.');
    }

    public function destroy(Product $product, ProductReview $review)
    {
        $this->ensureReviewBelongsToProduct($product, $review);

        $this->deleteStoredFiles($review->image_paths ?? []);
        $review->delete();

        return redirect()
            ->route('product-reviews.reviews.index', $product->id)
            ->with('success', 'Review deleted.');
    }

    protected function validateReview(Request $request): array
    {
        return $request->validate([
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'string', 'email', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'long_description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);
    }

    protected function storeImages(Request $request): array
    {
        if (!$request->hasFile('images')) {
            return [];
        }

        return collect($request->file('images'))
            ->map(fn ($file) => $file->store('product-reviews', 'public'))
            ->values()
            ->all();
    }

    protected function deleteStoredFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteStoredFile($path);
        }
    }

    protected function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function ensureReviewBelongsToProduct(Product $product, ProductReview $review): void
    {
        if ((int) $review->product_id !== (int) $product->id) {
            abort(404);
        }
    }
}
