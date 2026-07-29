<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Models\ShoeProduct;
use App\Models\ShoeSizeType;
use App\Models\ShoeSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ShoeProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Products/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Products/partials/CreateUpdate', [
            'mode' => 'create',
            'product' => null,
        ]);
    }

    public function edit(ShoeProduct $product)
    {
        return Inertia::render('Shoes/Products/partials/CreateUpdate', [
            'mode' => 'edit',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'brand_id' => $product->brand_id,
                
                'category_id' => $product->category_id,
                'subcategory_id' => $product->subcategory_id,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'model_code' => $product->model_code,
                'short_description' => $product->short_description,
                'full_description' => $product->full_description,
                'status' => $product->status,
                'featured' => (bool) $product->featured,
                'new_arrival' => (bool) $product->new_arrival,
                'best_seller' => (bool) $product->best_seller,
                'regular_price' => $product->regular_price,
                'sale_price' => $product->sale_price,
                'cost_price' => $product->cost_price,
                'currency' => $product->currency,
                'tax_class' => $product->tax_class,
                'discount_type' => $product->discount_type,
                'discount_value' => $product->discount_value,
                'sale_start_date' => optional($product->sale_start_date)->format('Y-m-d'),
                'sale_end_date' => optional($product->sale_end_date)->format('Y-m-d'),
                'stock_quantity' => $product->stock_quantity,
                'low_stock_alert_quantity' => $product->low_stock_alert_quantity,
                'stock_status' => $product->stock_status,
                'gender' => $product->gender,
                'age_group' => $product->age_group,
                'size_type_ids' => array_map('intval', $product->size_type_ids ?? []),
                'sizes_by_type' => collect($product->sizes_by_type ?? [])
                    ->map(fn ($item) => [
                        'type' => $item['type_name'] ?? $item['type_code'] ?? $item['type'] ?? '',
                        'sizes' => array_values($item['sizes'] ?? []),
                    ])
                    ->values()
                    ->all(),
                'color_ids' => array_map('intval', $product->color_ids ?? []),
                'material_ids' => array_map('intval', $product->material_ids ?? []),
                'shoe_weight' => $product->shoe_weight,
                 'product_video_url' => $product->product_video_url,
                 'thumbnail_url' => $product->thumbnail_url,
                 'gallery_urls' => $product->gallery_urls,
                 'gallery_paths' => array_values($product->gallery_images ?? []),
                 'hover_image_url' => $product->hover_image_url,
             ],
         ]);
     }


    public function generateSku(Request $request)
{
    $name = (string) $request->input('name', '');

    return response()->json([
        'sku' => $this->makeUniqueSku($name),
    ]);
}
    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = ShoeProduct::query()
    ->with([
        'brand:id,name',
        'category:id,name',
        'subcategory:id,name',
    ]);

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('slug', 'like', "%{$searchValue}%")
                    ->orWhere('sku', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhereHas('brand', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    
                    ->orWhereHas('category', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    ->orWhereHas('subcategory', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"));
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'status',
            3 => 'regular_price',
            4 => 'stock_quantity',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (ShoeProduct $product) {
            $statusBadge = match ($product->status) {
                'published' => '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Published</span>',
                'draft' => '<span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">Draft</span>',
                'out_of_stock' => '<span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">Out of Stock</span>',
                default => '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Archived</span>',
            };

            $thumbHtml = $product->thumbnail_url
                ? '<div class="flex items-center gap-3">
                        <img src="' . e($product->thumbnail_url) . '" alt="' . e($product->name) . '" class="h-12 w-12 rounded-xl border border-neutral-200 object-cover" />
                        <div>
                            <div class="font-medium text-neutral-800">' . e($product->name) . '</div>
                            <div class="text-xs text-neutral-500">' . e($product->slug) . '</div>
                        </div>
                   </div>'
                : '<div>
                        <div class="font-medium text-neutral-800">' . e($product->name) . '</div>
                        <div class="text-xs text-neutral-500">' . e($product->slug) . '</div>
                   </div>';

           $details = '
    <div class="text-sm">
        <div><span class="font-medium">Brand:</span> ' . e($product->brand?->name ?? '-') . '</div>
        <div><span class="font-medium">Category:</span> ' . e($product->category?->name ?? '-') . '</div>
        <div><span class="font-medium">Subcategory:</span> ' . e($product->subcategory?->name ?? '-') . '</div>
    </div>
';
            $price = $product->sale_price ?: $product->regular_price;
            $priceDisplay = $price !== null
                ? e($product->currency . ' ' . number_format((float) $price, 2))
                : '-';

            $stockDisplay = $product->stock_quantity !== null
                ? (string) $product->stock_quantity
                : '-';

            $payload = e(json_encode([
                'id' => $product->id,
                'name' => $product->name,
                'status' => $product->status,
            ], JSON_UNESCAPED_UNICODE));
            $toggleLabel = $product->status === 'published' ? 'Deactivate' : 'Activate';
            $toggleClass = $product->status === 'published'
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
        $validated = $this->validateProduct($request);

        $product = new ShoeProduct();
        $payload = $this->buildPayload($request, $validated, null);
        $product->fill($payload);
        $product->save();

        return redirect()
            ->route('admin.shoes.products.index')
            ->with('success', 'Shoe product created.');
    }

    public function update(Request $request, ShoeProduct $product)
    {
        $validated = $this->validateProduct($request, $product);

        $payload = $this->buildPayload($request, $validated, $product);
        $product->fill($payload);
        $product->save();

        return redirect()
            ->route('admin.shoes.products.index')
            ->with('success', 'Shoe product updated.');
    }

    public function destroy(ShoeProduct $product)
    {
        $this->deleteStoredFile($product->thumbnail_path);
        $this->deleteStoredFile($product->hover_image_path);
        $this->deleteStoredFiles($product->gallery_images ?? []);

        $product->load('reviews');
        foreach ($product->reviews as $review) {
            $this->deleteStoredFiles($review->image_paths ?? []);
        }
        $product->reviews()->delete();

        $product->delete();

        return redirect()
            ->route('admin.shoes.products.index')
            ->with('success', 'Shoe product deleted.');
    }

    public function toggleStatus(ShoeProduct $product)
    {
        $product->status = $product->status === 'published' ? 'draft' : 'published';
        $product->save();

        return back()->with('success', 'Shoe product status updated.');
    }

    protected function validateProduct(Request $request, ?ShoeProduct $product = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shoe_products', 'slug')->ignore($product?->id),
            ],
            'brand_id' => ['required', 'integer', 'exists:shoe_brands,id'],
          'product_type_id' => ['nullable', 'integer', 'exists:shoe_types,id'],
            'category_id' => ['required', 'integer', 'exists:shoes_categories,id'],
            'subcategory_id' => ['required', 'integer', 'exists:shoe_subcategories,id'],
            'sku' => [
    'nullable',
    'string',
    'max:255',
    Rule::unique('shoe_products', 'sku')->ignore($product?->id),
],
            'barcode' => ['nullable', 'string', 'max:255'],
            'model_code' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'full_description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,out_of_stock,archived'],
            'featured' => ['nullable', 'boolean'],
            'new_arrival' => ['nullable', 'boolean'],
            'best_seller' => ['nullable', 'boolean'],
            'regular_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:20'],
            'tax_class' => ['nullable', 'string', 'max:100'],
            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'sale_start_date' => ['nullable', 'date'],
            'sale_end_date' => ['nullable', 'date'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
            'stock_status' => ['required', 'in:in_stock,out_of_stock,preorder'],
            'gender' => ['nullable', 'in:men,women,kids,unisex'],
            'age_group' => ['nullable', 'in:adult,teen,kids,baby'],
            'size_type_ids' => ['required', 'array', 'min:1'],
            'size_type_ids.*' => ['integer', 'exists:shoe_size_types,id'],
            'sizes_by_type' => ['required', 'array', 'min:1'],
            'sizes_by_type.*.type' => ['required', 'string', 'max:255'],
            'sizes_by_type.*.sizes' => ['required', 'array', 'min:1'],
            'sizes_by_type.*.sizes.*' => ['required', 'string', 'max:50'],
            'color_ids' => ['nullable', 'array'],
            'color_ids.*' => ['integer', 'exists:shoe_colors,id'],
            'material_ids' => ['nullable', 'array'],
            'material_ids.*' => ['integer', 'exists:shoe_materials,id'],
            'shoe_weight' => ['nullable', 'string', 'max:100'],
             'thumbnail_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
             'gallery_images' => ['nullable', 'array'],
             'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
             'gallery_remove_paths' => ['nullable', 'array'],
             'gallery_remove_paths.*' => ['string'],
             'clear_gallery' => ['nullable', 'boolean'],
             'hover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
             'product_video_url' => ['nullable', 'url', 'max:2048'],
         ]);

        $subcategory = ShoeSubcategory::query()->find($validated['subcategory_id']);

        if (!$subcategory || (int) $subcategory->category_id !== (int) $validated['category_id']) {
            throw ValidationException::withMessages([
                'subcategory_id' => 'Selected subcategory does not belong to the selected category.',
            ]);
        }

        if (
            !empty($validated['sale_start_date']) &&
            !empty($validated['sale_end_date']) &&
            strtotime($validated['sale_end_date']) < strtotime($validated['sale_start_date'])
        ) {
            throw ValidationException::withMessages([
                'sale_end_date' => 'Sale end date must be after or equal to sale start date.',
            ]);
        }

        return $validated;
    }

    protected function buildPayload(Request $request, array $validated, ?ShoeProduct $product = null): array
    {
        $normalizedSizesByType = $this->normalizeSizesByType(
            $validated['size_type_ids'] ?? [],
            $validated['sizes_by_type'] ?? []
        );

        if (count($normalizedSizesByType) !== count($validated['size_type_ids'] ?? [])) {
            throw ValidationException::withMessages([
                'sizes_by_type' => 'Each selected shoe size type must have at least one size.',
            ]);
        }

        $payload = [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'brand_id' => (int) $validated['brand_id'],
            'product_type_id' => !empty($validated['product_type_id'])
    ? (int) $validated['product_type_id']
    : null,
            'category_id' => (int) $validated['category_id'],
            'subcategory_id' => (int) $validated['subcategory_id'],
            'sku' => filled(trim((string) ($validated['sku'] ?? '')))
    ? strtoupper(trim((string) $validated['sku']))
    : $this->makeUniqueSku($validated['name'] ?? 'SHOE', $product?->id),

            'barcode' => $validated['barcode'] ?? null,
            'model_code' => $validated['model_code'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'full_description' => $validated['full_description'] ?? null,
            'status' => $validated['status'],
            'featured' => $request->boolean('featured'),
            'new_arrival' => $request->boolean('new_arrival'),
            'best_seller' => $request->boolean('best_seller'),
            'regular_price' => $validated['regular_price'] ?? null,
            'sale_price' => $validated['sale_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'currency' => $validated['currency'],
            'tax_class' => $validated['tax_class'] ?? null,
            'discount_type' => $validated['discount_type'] ?: null,
            'discount_value' => $validated['discount_value'] ?? null,
            'sale_start_date' => $validated['sale_start_date'] ?? null,
            'sale_end_date' => $validated['sale_end_date'] ?? null,
            'stock_quantity' => $validated['stock_quantity'] ?? null,
            'low_stock_alert_quantity' => $validated['low_stock_alert_quantity'] ?? null,
            'stock_status' => $validated['stock_status'],
            'gender' => $validated['gender'] ?? null,
            'age_group' => $validated['age_group'] ?? null,
            'size_type_ids' => array_values(array_map('intval', $validated['size_type_ids'] ?? [])),
            'sizes_by_type' => $normalizedSizesByType,
            'color_ids' => array_values(array_map('intval', $validated['color_ids'] ?? [])),
            'material_ids' => array_values(array_map('intval', $validated['material_ids'] ?? [])),
            'shoe_weight' => $validated['shoe_weight'] ?? null,
            'product_video_url' => $validated['product_video_url'] ?? null,
        ];

        if ($request->hasFile('thumbnail_image')) {
            if ($product?->thumbnail_path) {
                $this->deleteStoredFile($product->thumbnail_path);
            }

            $payload['thumbnail_path'] = $request->file('thumbnail_image')->store('shoe-products', 'public');
        } elseif (!$product) {
            $payload['thumbnail_path'] = null;
        }

        if ($request->hasFile('hover_image')) {
            if ($product?->hover_image_path) {
                $this->deleteStoredFile($product->hover_image_path);
            }

            $payload['hover_image_path'] = $request->file('hover_image')->store('shoe-products', 'public');
        } elseif (!$product) {
            $payload['hover_image_path'] = null;
        }

        $clearGallery = $request->boolean('clear_gallery');
        $removeGalleryPaths = array_values(array_filter((array) $request->input('gallery_remove_paths', [])));
        $galleryPaths = array_values(array_filter($product?->gallery_images ?? []));

        if ($product) {
            if ($clearGallery) {
                $this->deleteStoredFiles($galleryPaths);
                $galleryPaths = [];
            } elseif (count($removeGalleryPaths)) {
                $toRemove = array_values(array_intersect($galleryPaths, $removeGalleryPaths));

                $this->deleteStoredFiles($toRemove);
                $galleryPaths = array_values(array_diff($galleryPaths, $toRemove));
            }
        }

        if ($request->hasFile('gallery_images')) {
            $newPaths = collect($request->file('gallery_images'))
                ->map(fn ($file) => $file->store('shoe-products', 'public'))
                ->values()
                ->all();

            $galleryPaths = ($product && !$clearGallery)
                ? array_values(array_merge($galleryPaths, $newPaths))
                : $newPaths;
        }

        if ($product || $request->hasFile('gallery_images')) {
            $payload['gallery_images'] = $galleryPaths;
        } elseif (!$product) {
            $payload['gallery_images'] = [];
        }

        return $payload;
    }

    protected function normalizeSizesByType(array $sizeTypeIds, array $rawSizesByType): array
    {
        $types = ShoeSizeType::query()
            ->whereIn('id', $sizeTypeIds)
            ->get()
            ->keyBy('id');

        $rawCollection = collect($rawSizesByType);

        return collect($sizeTypeIds)
            ->map(function ($typeId) use ($types, $rawCollection) {
                $type = $types->get((int) $typeId);

                if (!$type) {
                    return null;
                }

                $entry = $rawCollection->first(function ($item) use ($type) {
                    $value = strtolower(trim((string) ($item['type'] ?? '')));

                    return $value === strtolower($type->name)
                        || $value === strtolower($type->code);
                });

                $sizes = collect($entry['sizes'] ?? [])
                    ->map(fn ($size) => trim((string) $size))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (empty($sizes)) {
                    return null;
                }

                return [
                    'type_id' => (int) $type->id,
                    'type_code' => $type->code,
                    'type_name' => $type->name,
                    'sizes' => $sizes,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function makeUniqueSku(?string $name = null, ?int $ignoreId = null): string
{
    $base = $this->buildSkuBase($name);

    do {
        $suffix = strtoupper(Str::random(6));
        $sku = $base . '-' . $suffix;

        $exists = ShoeProduct::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('sku', $sku)
            ->exists();
    } while ($exists);

    return $sku;
}

protected function buildSkuBase(?string $name = null): string
{
    $base = Str::upper(Str::slug((string) $name, '-'));
    $base = preg_replace('/[^A-Z0-9\-]/', '', $base ?? '');
    $base = trim((string) $base, '-');

    if ($base === '') {
        return 'SHOE';
    }

    $parts = array_filter(explode('-', $base));
    $short = collect($parts)
        ->take(3)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 4)))
        ->implode('-');

    return $short ?: 'SHOE';
}

    protected function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function deleteStoredFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteStoredFile($path);
        }
    }
}
