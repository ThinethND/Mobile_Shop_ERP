<?php

namespace App\Http\Controllers\Admin\Fashion;

use App\Http\Controllers\Controller;
use App\Models\FashionBrand;
use App\Models\FashionCategory;
use App\Models\FashionProduct;
use App\Models\FashionProductType;
use App\Models\WarrantyOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FashionProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Fashion/Products/index', $this->formOptions());
    }

    public function create()
    {
        return Inertia::render('Fashion/Products/partials/CreateUpdate', array_merge($this->formOptions(), [
            'mode' => 'create',
            'product' => null,
        ]));
    }

    public function edit(FashionProduct $product)
    {
        $product->load(['category:id,name', 'productType:id,name', 'brand:id,name', 'warrantyOption:id,name']);

        return Inertia::render('Fashion/Products/partials/CreateUpdate', array_merge($this->formOptions(), [
            'mode' => 'edit',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'product_type_id' => $product->product_type_id,
                'brand_id' => $product->brand_id,
                'brand_name' => $product->brand_name,
                'sku' => $product->sku,
                'target_gender' => $product->target_gender,
                'size_label' => $product->size_label,
                'color' => $product->color,
                'material' => $product->material,
                'style' => $product->style,
                'fit' => $product->fit,
                'lens_type' => $product->lens_type,
                'frame_material' => $product->frame_material,
                'bag_size' => $product->bag_size,
                'closure_type' => $product->closure_type,
                'strap_type' => $product->strap_type,
                'dimensions' => $product->dimensions,
                'care_instructions' => $product->care_instructions,
                'warranty_option_id' => $product->warranty_option_id,
                'warranty_period' => $product->warranty_period,
                'price' => (float) $product->price,
                'sale_price' => $product->sale_price !== null ? (float) $product->sale_price : null,
                'stock_quantity' => $product->stock_quantity,
                'low_stock_alert_quantity' => $product->low_stock_alert_quantity,
                'stock_status' => $product->stock_status,
                'status' => $product->status,
                'featured' => (bool) $product->featured,
                'today_best_deals' => (bool) $product->today_best_deals,
                'best_seller' => (bool) $product->best_seller,
                'short_description' => $product->short_description,
                'full_description' => $product->full_description,
                'product_video_url' => $product->product_video_url,
                'main_image_url' => $product->main_image_url,
                'hover_image_url' => $product->hover_image_url,
                'gallery_urls' => $product->gallery_urls,
                'gallery_paths' => array_values($product->gallery_image_paths ?: []),
            ],
        ]));
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = FashionProduct::query()
            ->with(['category:id,name', 'productType:id,name', 'brand:id,name', 'warrantyOption:id,name'])
            ->when($request->input('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->input('product_type_id'), fn ($query, $typeId) => $query->where('product_type_id', $typeId))
            ->when($request->input('brand_id'), fn ($query, $brandId) => $query->where('brand_id', $brandId))
            ->when($request->input('warranty_option_id'), fn ($query, $warrantyId) => $query->where('warranty_option_id', $warrantyId))
            ->when($request->input('stock_status'), fn ($query, $stockStatus) => $query->where('stock_status', $stockStatus));

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('brand_name', 'like', "%{$searchValue}%")
                    ->orWhere('sku', 'like', "%{$searchValue}%")
                    ->orWhere('target_gender', 'like', "%{$searchValue}%")
                    ->orWhere('size_label', 'like', "%{$searchValue}%")
                    ->orWhere('color', 'like', "%{$searchValue}%")
                    ->orWhere('material', 'like', "%{$searchValue}%")
                    ->orWhere('style', 'like', "%{$searchValue}%")
                    ->orWhere('lens_type', 'like', "%{$searchValue}%")
                    ->orWhere('bag_size', 'like', "%{$searchValue}%")
                    ->orWhereHas('category', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    ->orWhereHas('productType', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    ->orWhereHas('brand', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    ->orWhereHas('warrantyOption', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"));
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();
        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $columns = [
            0 => 'id',
            1 => 'name',
            3 => 'price',
            5 => 'stock_quantity',
            6 => 'status',
        ];
        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (FashionProduct $product) {
            $productHtml = $product->main_image_url
                ? '<div class="flex items-center gap-3"><img src="' . e($product->main_image_url) . '" alt="' . e($product->name) . '" class="h-12 w-12 rounded-xl border border-neutral-200 object-cover" /><div><div class="font-medium text-neutral-800">' . e($product->name) . '</div><div class="text-xs text-neutral-500">' . e($product->sku ?: '-') . '</div></div></div>'
                : '<div><div class="font-medium text-neutral-800">' . e($product->name) . '</div><div class="text-xs text-neutral-500">' . e($product->sku ?: '-') . '</div></div>';

            $details = '<div class="text-xs text-neutral-600 space-y-1">
                    <div><span class="font-medium text-neutral-700">Category:</span> ' . e($product->category?->name ?? '-') . '</div>
                    <div><span class="font-medium text-neutral-700">Type:</span> ' . e($product->productType?->name ?? '-') . '</div>
                    <div><span class="font-medium text-neutral-700">Brand:</span> ' . e($this->brandDisplay($product)) . '</div>
                    <div><span class="font-medium text-neutral-700">Color / Size:</span> ' . e($this->compactDisplay([$product->color, $product->size_label])) . '</div>
                    <div><span class="font-medium text-neutral-700">Warranty:</span> ' . e($this->warrantyDisplay($product)) . '</div>
                </div>';

            $priceDisplay = $product->sale_price !== null
                ? '<div><span class="font-semibold text-neutral-800">LKR ' . e(number_format((float) $product->sale_price, 2)) . '</span><div class="text-xs text-neutral-400 line-through">LKR ' . e(number_format((float) $product->price, 2)) . '</div></div>'
                : '<span class="font-semibold text-neutral-800">LKR ' . e(number_format((float) $product->price, 2)) . '</span>';

            $stockStatus = ucwords(str_replace('_', ' ', (string) $product->stock_status));
            $statusBadge = $product->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

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
                    <button type="button" data-action="toggle" data-payload=\'' . $payload . '\' class="rounded-full border px-3 py-1.5 text-xs font-medium ' . $toggleClass . '">' . $toggleLabel . '</button>
                    <button type="button" data-action="edit" data-payload=\'' . $payload . '\' class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">Edit</button>
                    <button type="button" data-action="delete" data-payload=\'' . $payload . '\' class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
                </div>
            ';

            return [
                'id' => $product->id,
                'product_info' => $productHtml,
                'details' => $details,
                'price_display' => $priceDisplay,
                'stock_status' => e($stockStatus),
                'stock_display' => (string) $product->stock_quantity,
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
        $validated = $this->validateProduct($request);
        $mainPath = $request->hasFile('main_image')
            ? $request->file('main_image')->store('fashion/products', 'public')
            : null;
        $hoverPath = $request->hasFile('hover_image')
            ? $request->file('hover_image')->store('fashion/products/hover', 'public')
            : null;
        $galleryPaths = $this->storeGalleryImages($request);

        DB::transaction(function () use ($validated, $mainPath, $hoverPath, $galleryPaths) {
            FashionProduct::create([
                ...$validated,
                'slug' => $this->uniqueSlug($validated['name']),
                'featured' => (bool) ($validated['featured'] ?? false),
                'today_best_deals' => (bool) ($validated['today_best_deals'] ?? false),
                'best_seller' => (bool) ($validated['best_seller'] ?? false),
                'main_image_path' => $mainPath,
                'hover_image_path' => $hoverPath,
                'gallery_image_paths' => $galleryPaths ?: null,
            ]);
        });

        return redirect()
            ->route('admin.fashion.products.index')
            ->with('success', 'Fashion product created.');
    }

    public function update(Request $request, FashionProduct $product)
    {
        $validated = $this->validateProduct($request, $product);

        if ($request->hasFile('main_image')) {
            $this->deleteStoredFile($product->main_image_path);
            $product->main_image_path = $request->file('main_image')->store('fashion/products', 'public');
        }

        if ($request->hasFile('hover_image')) {
            $this->deleteStoredFile($product->hover_image_path);
            $product->hover_image_path = $request->file('hover_image')->store('fashion/products/hover', 'public');
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

        $newGalleryPaths = $this->storeGalleryImages($request);
        $galleryPaths = $clearGallery ? $newGalleryPaths : array_values(array_merge($galleryPaths, $newGalleryPaths));
        $product->gallery_image_paths = count($galleryPaths) ? $galleryPaths : null;

        DB::transaction(function () use ($product, $validated) {
            $product->fill([
                ...$validated,
                'featured' => (bool) ($validated['featured'] ?? false),
                'today_best_deals' => (bool) ($validated['today_best_deals'] ?? false),
                'best_seller' => (bool) ($validated['best_seller'] ?? false),
            ]);

            if (!filled($product->slug)) {
                $product->slug = $this->uniqueSlug($validated['name'], $product->id);
            }

            $product->save();
        });

        return redirect()
            ->route('admin.fashion.products.index')
            ->with('success', 'Fashion product updated.');
    }

    public function destroy(FashionProduct $product)
    {
        $this->deleteStoredFile($product->main_image_path);
        $this->deleteStoredFile($product->hover_image_path);
        $this->deleteStoredFiles($product->gallery_image_paths ?: []);
        $product->delete();

        return redirect()
            ->route('admin.fashion.products.index')
            ->with('success', 'Fashion product deleted.');
    }

    public function toggleStatus(FashionProduct $product)
    {
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return back()->with('success', 'Fashion product status updated.');
    }

    protected function validateProduct(Request $request, ?FashionProduct $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:fashion_categories,id'],
            'product_type_id' => ['required', 'integer', 'exists:fashion_product_types,id'],
            'brand_id' => ['nullable', 'integer', 'exists:fashion_brands,id'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('fashion_products', 'sku')->ignore($product?->id)],
            'target_gender' => ['nullable', 'string', 'max:50'],
            'size_label' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'material' => ['nullable', 'string', 'max:255'],
            'style' => ['nullable', 'string', 'max:255'],
            'fit' => ['nullable', 'string', 'max:255'],
            'lens_type' => ['nullable', 'string', 'max:255'],
            'frame_material' => ['nullable', 'string', 'max:255'],
            'bag_size' => ['nullable', 'string', 'max:255'],
            'closure_type' => ['nullable', 'string', 'max:255'],
            'strap_type' => ['nullable', 'string', 'max:255'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'care_instructions' => ['nullable', 'string', 'max:2000'],
            'warranty_option_id' => ['nullable', 'integer', 'exists:warranty_options,id'],
            'warranty_period' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
            'stock_status' => ['required', 'in:in_stock,out_of_stock,pre_order,discontinued'],
            'status' => ['required', 'in:active,inactive'],
            'featured' => ['nullable', 'boolean'],
            'today_best_deals' => ['nullable', 'boolean'],
            'best_seller' => ['nullable', 'boolean'],
            'short_description' => ['nullable', 'string', 'max:1200'],
            'full_description' => ['nullable', 'string'],
            'product_video_url' => ['nullable', 'url', 'max:2048'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_remove_paths' => ['nullable', 'array'],
            'gallery_remove_paths.*' => ['string'],
            'clear_gallery' => ['nullable', 'boolean'],
        ]);
    }

    protected function formOptions(): array
    {
        return [
            'categories' => FashionCategory::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (FashionCategory $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'value' => $category->id,
                    'label' => $category->name,
                ])
                ->values(),
            'brands' => FashionBrand::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (FashionBrand $brand) => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'value' => $brand->id,
                    'label' => $brand->name,
                ])
                ->values(),
            'productTypes' => FashionProductType::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'category_id'])
                ->map(fn (FashionProductType $type) => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'category_id' => $type->category_id,
                    'value' => $type->id,
                    'label' => $type->name,
                ])
                ->values(),
            'warranties' => WarrantyOption::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (WarrantyOption $warranty) => [
                    'id' => $warranty->id,
                    'name' => $warranty->name,
                    'value' => $warranty->id,
                    'label' => $warranty->name,
                ])
                ->values(),
        ];
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'fashion-product';
        $slug = $base;
        $index = 2;

        while (FashionProduct::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$index}";
            $index++;
        }

        return $slug;
    }

    protected function storeGalleryImages(Request $request): array
    {
        if (!$request->hasFile('gallery_images')) {
            return [];
        }

        return collect($request->file('gallery_images', []))
            ->map(fn ($file) => $file->store('fashion/products/gallery', 'public'))
            ->values()
            ->all();
    }

    protected function warrantyDisplay(FashionProduct $product): string
    {
        return $this->compactDisplay([
            $product->warrantyOption?->name,
            $product->warranty_period,
        ]);
    }

    protected function brandDisplay(FashionProduct $product): string
    {
        return $product->brand?->name ?: ($product->brand_name ?: '-');
    }

    protected function compactDisplay(array $values): string
    {
        return trim(implode(' / ', array_filter($values))) ?: '-';
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
