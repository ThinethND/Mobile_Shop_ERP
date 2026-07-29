<?php

namespace App\Http\Controllers\Admin\Cosmetics;

use App\Http\Controllers\Controller;
use App\Models\CosmeticProduct;
use App\Models\CosmeticProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CosmeticProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Cosmetics/CosmeticProducts/index');
    }

    public function create()
    {
        return Inertia::render('Cosmetics/CosmeticProducts/partials/CreateUpdate', [
            'mode' => 'create',
            'product' => null,
        ]);
    }

    public function edit(CosmeticProduct $product)
    {
        $product->load([
            'brand:id,name',
            'category:id,name',
            'productType:id,name,cosmetic_category_id',
            'countryOfOrigin:id,name',
            'variants:id,cosmetic_product_id,cosmetic_size_volume_id,price,stock_count,sku,status',
        ]);

        return Inertia::render('Cosmetics/CosmeticProducts/partials/CreateUpdate', [
            'mode' => 'edit',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'brand_id' => $product->brand_id,
                'category_id' => $product->category_id,
                'product_type_id' => $product->product_type_id,
                'country_of_origin_id' => $product->country_of_origin_id,
                'size_volume_ids' => array_map('intval', $product->size_volume_ids ?? []),
                'batch_number' => $product->batch_number,
                'manufacture_date' => optional($product->manufacture_date)->format('Y-m-d'),
                'expiry_date' => optional($product->expiry_date)->format('Y-m-d'),
                'price' => (float) $product->price,
                'stock' => $product->stock,
                'discount_type' => $product->discount_type,
                'discount_value' => $product->discount_value,
                'is_featured' => (bool) $product->is_featured,
                'hot_deals' => (bool) $product->hot_deals,
                'best_selling' => (bool) $product->best_selling,
                'status' => $product->status,
                'short_description' => $product->short_description,
                'long_description' => $product->long_description,
                'product_video_url' => $product->product_video_url,
                'main_image_url' => $product->main_image_url,
                'hover_image_url' => $product->hover_image_url,
                'gallery_urls' => $product->gallery_urls,
                'gallery_paths' => array_values($product->gallery_image_paths ?: []),
                'variants' => $product->variants
                    ->map(fn ($v) => [
                        'id' => $v->id,
                        'cosmetic_size_volume_id' => (int) $v->cosmetic_size_volume_id,
                        'price' => $v->price,
                        'stock_count' => $v->stock_count,
                        'sku' => $v->sku,
                        'status' => $v->status,
                    ])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = CosmeticProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
                'productType:id,name',
                'countryOfOrigin:id,name',
            ]);

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('batch_number', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhereHas('brand', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    ->orWhereHas('category', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    ->orWhereHas('productType', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    ->orWhereHas('countryOfOrigin', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"));
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'name', // product_info column
            2 => 'name', // details column fallback
            3 => 'status',
            4 => 'price',
            5 => 'stock',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (CosmeticProduct $product) {
            $thumbUrl = $product->main_image_url;
            $thumbHtml = $thumbUrl
                ? '<div class="flex items-center gap-3">
                        <img src="' . e($thumbUrl) . '" alt="' . e($product->name) . '" class="h-12 w-12 rounded-xl border border-neutral-200 bg-neutral-50 object-cover" />
                        <div>
                            <div class="font-semibold text-neutral-800">' . e($product->name) . '</div>
                            <div class="text-xs text-neutral-500">#' . e((string) $product->id) . '</div>
                        </div>
                   </div>'
                : '<div>
                        <div class="font-semibold text-neutral-800">' . e($product->name) . '</div>
                        <div class="text-xs text-neutral-500">#' . e((string) $product->id) . '</div>
                   </div>';

            $details = '<div class="text-xs text-neutral-600 space-y-1">
                    <div><span class="font-medium text-neutral-700">Brand:</span> ' . e($product->brand?->name ?? '-') . '</div>
                    <div><span class="font-medium text-neutral-700">Category:</span> ' . e($product->category?->name ?? '-') . '</div>
                    <div><span class="font-medium text-neutral-700">Type:</span> ' . e($product->productType?->name ?? '-') . '</div>
                    <div><span class="font-medium text-neutral-700">Origin:</span> ' . e($product->countryOfOrigin?->name ?? '-') . '</div>
                </div>';

            $statusBadge = $product->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $priceDisplay = '<span class="font-semibold text-neutral-800">' . e(number_format((float) $product->price, 2)) . '</span>';

            $stockDisplay = $product->stock === null
                ? '<span class="text-neutral-500">-</span>'
                : '<span class="font-semibold text-neutral-800">' . e((string) $product->stock) . '</span>';

            $payload = e(json_encode([
                'id' => $product->id,
                'name' => $product->name,
                'status' => $product->status,
            ], JSON_UNESCAPED_UNICODE));
            $toggleLabel = $product->status === 'active' ? 'Deactivate' : 'Activate';
            $toggleClass = $product->status === 'active'
                ? 'border-amber-200 text-amber-700 hover:bg-amber-50'
                : 'border-green-200 text-green-700 hover:bg-green-50';

            $actions = '
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        data-action="toggle"
                        data-payload=\'' . $payload . '\'
                        class="rounded-full border px-3 py-1.5 text-xs font-medium ' . $toggleClass . '"
                    >
                        ' . $toggleLabel . '
                    </button>

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
                'id' => $product->id,
                'product_info' => $thumbHtml,
                'details' => $details,
                'status_badge' => $statusBadge,
                'price_display' => $priceDisplay,
                'stock_display' => $stockDisplay,
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
        $request->merge([
            'size_volume_ids' => $this->normalizeIdArray($request->input('size_volume_ids', [])),
            'variants' => $this->normalizeVariants($request->input('variants', [])),
        ]);

        $validated = $this->validateProduct($request);
        $this->assertProductTypeMatchesCategory($validated['product_type_id'], $validated['category_id']);
        $this->assertExpiryAfterManufacture($validated['manufacture_date'] ?? null, $validated['expiry_date'] ?? null);

        $mainPath = null;
        if ($request->hasFile('main_image')) {
            $mainPath = $request->file('main_image')->store('cosmetic-products', 'public');
        }

        $hoverPath = null;
        if ($request->hasFile('hover_image')) {
            $hoverPath = $request->file('hover_image')->store('cosmetic-products/hover', 'public');
        }

        $galleryPaths = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $img) {
                $galleryPaths[] = $img->store('cosmetic-products/gallery', 'public');
            }
        }

        DB::transaction(function () use ($validated, $mainPath, $hoverPath, $galleryPaths) {
            $product = CosmeticProduct::create([
                ...$validated,
                'size_volume_ids' => $validated['size_volume_ids'] ?? [],
                'slug' => $this->makeUniqueSlug((string) ($validated['name'] ?? '')),
                'discount_type' => $validated['discount_type'] ?: null,
                'discount_value' => $validated['discount_type'] ? ($validated['discount_value'] ?? null) : null,
                'is_featured' => (bool) ($validated['is_featured'] ?? false),
                'hot_deals' => (bool) ($validated['hot_deals'] ?? false),
                'best_selling' => (bool) ($validated['best_selling'] ?? false),
                'main_image_path' => $mainPath,
                'hover_image_path' => $hoverPath,
                'gallery_image_paths' => $galleryPaths ?: null,
            ]);

            $this->syncVariants($product, $validated['variants'] ?? []);
        });

        return redirect()
            ->route('admin.cosmetics.products.index')
            ->with('success', 'Cosmetic product created.');
    }

    public function update(Request $request, CosmeticProduct $product)
    {
        $request->merge([
            'size_volume_ids' => $this->normalizeIdArray($request->input('size_volume_ids', [])),
            'variants' => $this->normalizeVariants($request->input('variants', [])),
        ]);

        $validated = $this->validateProduct($request, $product->id);
        $this->assertProductTypeMatchesCategory($validated['product_type_id'], $validated['category_id']);
        $this->assertExpiryAfterManufacture($validated['manufacture_date'] ?? null, $validated['expiry_date'] ?? null);

        if ($request->hasFile('main_image')) {
            $this->deleteStoredFile($product->main_image_path);
            $product->main_image_path = $request->file('main_image')->store('cosmetic-products', 'public');
        }

        if ($request->hasFile('hover_image')) {
            $this->deleteStoredFile($product->hover_image_path);
            $product->hover_image_path = $request->file('hover_image')->store('cosmetic-products/hover', 'public');
        }

        $clearGallery = (bool) $request->input('clear_gallery', false);
        $removeGalleryPaths = array_values(array_filter((array) $request->input('gallery_remove_paths', [])));
        $galleryPaths = array_values(array_filter($product->gallery_image_paths ?: []));

        if ($clearGallery) {
            $this->deleteStoredFiles($galleryPaths);
            $galleryPaths = [];
        } elseif (count($removeGalleryPaths)) {
            $toRemove = array_values(array_intersect($galleryPaths, $removeGalleryPaths));

            $this->deleteStoredFiles($toRemove);
            $galleryPaths = array_values(array_diff($galleryPaths, $toRemove));
        }

        if ($request->hasFile('gallery_images')) {
            $newPaths = [];
            foreach ($request->file('gallery_images') as $img) {
                $newPaths[] = $img->store('cosmetic-products/gallery', 'public');
            }

            $galleryPaths = $clearGallery
                ? $newPaths
                : array_values(array_merge($galleryPaths, $newPaths));
        }

        $product->gallery_image_paths = count($galleryPaths) ? $galleryPaths : null;

        DB::transaction(function () use ($product, $validated) {
            $product->fill([
                ...$validated,
                'size_volume_ids' => $validated['size_volume_ids'] ?? [],
                'discount_type' => $validated['discount_type'] ?: null,
                'discount_value' => $validated['discount_type'] ? ($validated['discount_value'] ?? null) : null,
                'is_featured' => (bool) ($validated['is_featured'] ?? false),
                'hot_deals' => (bool) ($validated['hot_deals'] ?? false),
                'best_selling' => (bool) ($validated['best_selling'] ?? false),
            ]);

            if (!filled($product->slug)) {
                $product->slug = $this->makeUniqueSlug((string) ($validated['name'] ?? ''), $product->id);
            }

            $product->save();

            $this->syncVariants($product, $validated['variants'] ?? []);
        });

        return redirect()
            ->route('admin.cosmetics.products.index')
            ->with('success', 'Cosmetic product updated.');
    }

    public function destroy(CosmeticProduct $product)
    {
        $this->deleteStoredFile($product->main_image_path);
        $this->deleteStoredFile($product->hover_image_path);
        $this->deleteStoredFiles($product->gallery_image_paths ?: []);

        $product->load('reviews');
        foreach ($product->reviews as $review) {
            $this->deleteStoredFiles($review->image_paths ?? []);
        }
        $product->reviews()->delete();

        $product->variants()->delete();
        $product->delete();

        return redirect()
            ->route('admin.cosmetics.products.index')
            ->with('success', 'Cosmetic product deleted.');
    }

    public function toggleStatus(CosmeticProduct $product)
    {
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return back()->with('success', 'Cosmetic product status updated.');
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'brand_id' => ['required', 'integer', 'exists:cosmetic_brands,id'],
            'category_id' => ['required', 'integer', 'exists:cosmetic_categories,id'],
            'product_type_id' => ['required', 'integer', 'exists:cosmetic_product_types,id'],
            'country_of_origin_id' => ['required', 'integer', 'exists:cosmetic_countries_of_origin,id'],

            'size_volume_ids' => ['nullable', 'array'],
            'size_volume_ids.*' => ['integer', 'exists:cosmetic_size_volumes,id'],

            'batch_number' => ['nullable', 'string', 'max:255'],
            'manufacture_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],

            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],

            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],

            'is_featured' => ['nullable', 'boolean'],
            'hot_deals' => ['nullable', 'boolean'],
            'best_selling' => ['nullable', 'boolean'],

            'status' => ['nullable', 'in:active,inactive'],

            'short_description' => ['nullable', 'string'],
            'long_description' => ['nullable', 'string'],
            'product_video_url' => ['nullable', 'url', 'max:2048'],

            'variants' => ['nullable', 'array'],
            'variants.*.cosmetic_size_volume_id' => ['required', 'integer', 'exists:cosmetic_size_volumes,id'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.stock_count' => ['nullable', 'integer', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.status' => ['nullable', 'in:active,inactive'],

            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_remove_paths' => ['nullable', 'array'],
            'gallery_remove_paths.*' => ['string'],
            'clear_gallery' => ['nullable', 'boolean'],
        ]);
    }

    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? substr($base, 0, 220) : 'cosmetic-product';

        $candidate = $base;
        $counter = 1;

        while (
            CosmeticProduct::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = substr($base, 0, 210) . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function normalizeIdArray($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            } else {
                $value = [$value];
            }
        }

        return collect((array) $value)
            ->flatten()
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeVariants($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            } else {
                $value = [];
            }
        }

        return collect((array) $value)
            ->map(function ($row) {
                $row = (array) $row;

                return [
                    'cosmetic_size_volume_id' => isset($row['cosmetic_size_volume_id']) && $row['cosmetic_size_volume_id'] !== ''
                        ? (int) $row['cosmetic_size_volume_id']
                        : null,

                    'price' => isset($row['price']) && $row['price'] !== ''
                        ? (float) $row['price']
                        : null,

                    'stock_count' => isset($row['stock_count']) && $row['stock_count'] !== ''
                        ? (int) $row['stock_count']
                        : null,

                    'sku' => !empty($row['sku']) ? trim((string) $row['sku']) : null,
                    'status' => in_array(($row['status'] ?? 'active'), ['active', 'inactive'], true)
                        ? $row['status']
                        : 'active',
                ];
            })
            ->filter(fn ($row) => $row['cosmetic_size_volume_id'])
            ->unique(fn ($row) => (string) $row['cosmetic_size_volume_id'])
            ->values()
            ->all();
    }

    private function syncVariants(CosmeticProduct $product, array $variants): void
    {
        $allowedSizeIds = collect($product->size_volume_ids ?? [])->map(fn ($id) => (int) $id)->all();

        $rows = collect($variants)
            ->filter(fn ($row) => in_array((int) $row['cosmetic_size_volume_id'], $allowedSizeIds, true))
            ->map(function ($row) {
                return [
                    'cosmetic_size_volume_id' => (int) $row['cosmetic_size_volume_id'],
                    'price' => (float) $row['price'],
                    'stock_count' => $row['stock_count'],
                    'sku' => $row['sku'],
                    'status' => $row['status'] ?? 'active',
                ];
            })
            ->values()
            ->all();

        $product->variants()->delete();

        if (!empty($rows)) {
            $product->variants()->createMany($rows);
        }
    }

    private function assertProductTypeMatchesCategory(int $productTypeId, int $categoryId): void
    {
        $type = CosmeticProductType::query()->find($productTypeId);

        if (!$type || (int) $type->cosmetic_category_id !== (int) $categoryId) {
            throw ValidationException::withMessages([
                'product_type_id' => 'Selected product type does not belong to the selected cosmetic category.',
            ]);
        }
    }

    private function assertExpiryAfterManufacture(?string $manufactureDate, ?string $expiryDate): void
    {
        if (!$manufactureDate || !$expiryDate) {
            return;
        }

        if (strtotime($expiryDate) < strtotime($manufactureDate)) {
            throw ValidationException::withMessages([
                'expiry_date' => 'Expiry date must be after or equal to manufacture date.',
            ]);
        }
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function deleteStoredFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteStoredFile($path);
        }
    }
}
