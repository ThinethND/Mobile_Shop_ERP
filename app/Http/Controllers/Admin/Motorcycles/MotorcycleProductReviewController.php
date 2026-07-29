<?php

namespace App\Http\Controllers\Admin\Motorcycles;

use App\Http\Controllers\Controller;
use App\Models\MotorcycleProduct;
use App\Models\MotorcycleProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MotorcycleProductReviewController extends Controller
{
    public function index()
    {
        return Inertia::render('Motorcycles/ProductReviews/index');
    }

    public function create()
    {
        return Inertia::render('Motorcycles/ProductReviews/partials/CreateUpdate', [
            'mode' => 'create',
            'review' => null,
            'products' => $this->productOptions(),
        ]);
    }

    public function edit(MotorcycleProductReview $review)
    {
        return Inertia::render('Motorcycles/ProductReviews/partials/CreateUpdate', [
            'mode' => 'edit',
            'review' => [
                'id' => $review->id,
                'product_id' => $review->product_id,
                'rating' => $review->rating,
                'customer_name' => $review->customer_name,
                'customer_email' => $review->customer_email,
                'short_description' => $review->short_description,
                'long_description' => $review->long_description,
                'image_urls' => $review->image_urls,
                'status' => $review->status,
            ],
            'products' => $this->productOptions(),
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = MotorcycleProductReview::query()->with('product:id,name,sku,main_image_path');
        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('customer_name', 'like', "%{$searchValue}%")
                    ->orWhere('customer_email', 'like', "%{$searchValue}%")
                    ->orWhere('short_description', 'like', "%{$searchValue}%")
                    ->orWhereHas('product', fn ($x) => $x->where('name', 'like', "%{$searchValue}%")->orWhere('sku', 'like', "%{$searchValue}%"));
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();
        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $columns = [
            0 => 'id',
            2 => 'rating',
            5 => 'status',
        ];
        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (MotorcycleProductReview $review) {
            $payload = e(json_encode([
                'id' => $review->id,
                'name' => $review->customer_name ?: 'Review #' . $review->id,
            ], JSON_UNESCAPED_UNICODE));

            $product = $review->product;
            $productHtml = '<div><div class="font-medium text-neutral-800">' . e($product?->name ?? '-') . '</div><div class="text-xs text-neutral-500">' . e($product?->sku ?? '-') . '</div></div>';
            $customerHtml = '<div><div class="font-medium text-neutral-800">' . e($review->customer_name ?: '-') . '</div><div class="text-xs text-neutral-500">' . e($review->customer_email ?: '-') . '</div></div>';
            $ratingHtml = $review->rating
                ? '<span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800">' . e((string) $review->rating) . ' / 5</span>'
                : '<span class="text-xs text-neutral-400">-</span>';
            $statusBadge = $review->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $short = (string) ($review->short_description ?? '');
            $short = mb_strlen($short) > 90 ? mb_substr($short, 0, 87) . '...' : $short;

            $actions = '
                <div class="flex items-center gap-2">
                    <button type="button" data-action="edit" data-payload=\'' . $payload . '\' class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">Edit</button>
                    <button type="button" data-action="delete" data-payload=\'' . $payload . '\' class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
                </div>
            ';

            return [
                'id' => $review->id,
                'product' => $productHtml,
                'rating' => $ratingHtml,
                'customer' => $customerHtml,
                'summary' => $short !== '' ? e($short) : '<span class="text-neutral-400">-</span>',
                'status_badge' => $statusBadge,
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

    public function store(Request $request)
    {
        $validated = $this->validateReview($request);
        MotorcycleProductReview::create($this->payload($request, $validated));

        return redirect()
            ->route('admin.motorcycles.product-reviews.index')
            ->with('success', 'Product review created.');
    }

    public function update(Request $request, MotorcycleProductReview $review)
    {
        $validated = $this->validateReview($request);
        $review->update($this->payload($request, $validated, $review));

        return redirect()
            ->route('admin.motorcycles.product-reviews.index')
            ->with('success', 'Product review updated.');
    }

    public function destroy(MotorcycleProductReview $review)
    {
        $this->deleteFiles($review->image_paths ?? []);
        $review->delete();

        return redirect()
            ->route('admin.motorcycles.product-reviews.index')
            ->with('success', 'Product review deleted.');
    }

    protected function validateReview(Request $request): array
    {
        return $request->validate([
            'product_id' => ['required', 'integer', 'exists:motorcycle_products,id'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:1200'],
            'long_description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    protected function payload(Request $request, array $validated, ?MotorcycleProductReview $review = null): array
    {
        $images = $review?->image_paths ?? [];

        if ($request->hasFile('images')) {
            $this->deleteFiles($images);
            $images = collect($request->file('images', []))
                ->map(fn ($file) => $file->store('motorcycle/reviews', 'public'))
                ->values()
                ->all();
        }

        return [
            'product_id' => $validated['product_id'],
            'rating' => $validated['rating'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_email' => $validated['customer_email'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'long_description' => $validated['long_description'] ?? null,
            'image_paths' => $images,
            'status' => $validated['status'],
        ];
    }

    protected function productOptions()
    {
        return MotorcycleProduct::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sku'])
            ->map(fn (MotorcycleProduct $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'value' => $product->id,
                'label' => $product->name . ($product->sku ? ' (' . $product->sku . ')' : ''),
            ])
            ->values();
    }

    protected function deleteFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
