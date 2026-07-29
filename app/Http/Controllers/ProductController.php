<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ColorOption;
use App\Models\Product;
use App\Models\RamOption;
use App\Models\StorageOption;
use App\Models\WarrantyOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $brands = Brand::with('categories:id')->get()->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'category_ids' => $b->categories->pluck('id')->values(),
        ]);

        return Inertia::render('Products/index', [
            'categories' => Category::select('id', 'name')->orderBy('name')->get(),
            'brands' => $brands,
            'colors' => ColorOption::select('id', 'name', 'color_code')
                ->orderBy('name')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'color_code' => $c->color_code,
                    'image_url' => $c->image_url,
                ]),
            'warranties' => WarrantyOption::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $brands = Brand::with('categories:id')->get()->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'category_ids' => $b->categories->pluck('id')->values(),
        ]);

        return Inertia::render('Products/CreateUpdate', [
            'mode' => 'create',
            'product' => null,
            'categories' => Category::select('id', 'name')->orderBy('name')->get(),
            'brands' => $brands,
            'colors' => ColorOption::select('id', 'name', 'color_code')
                ->orderBy('name')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'color_code' => $c->color_code,
                    'image_url' => $c->image_url,
                ]),
            'warranties' => WarrantyOption::select('id', 'name')->orderBy('name')->get(),
            'storages' => StorageOption::orderBy('value')->get()->map(fn ($s) => [
                'id' => $s->id,
                'label' => trim($s->value . ' ' . $s->unit),
            ]),
            'rams' => RamOption::orderBy('value')->get()->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim($r->value . ' ' . ($r->unit ?? 'GB')),
            ]),
        ]);
    }

    public function edit(Product $product)
    {
        $product->load([
            'colors:id',
            'category:id,name',
            'brand:id,name',
            'variants:id,product_id,storage_option_id,color_option_id,price_lkr,stock_count,sku,status',
        ]);

        $brands = Brand::with('categories:id')->get()->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'category_ids' => $b->categories->pluck('id')->values(),
        ]);

        $storageIds = is_array($product->storage_option_ids) && count($product->storage_option_ids)
            ? $product->storage_option_ids
            : ($product->storage_option_id ? [(int) $product->storage_option_id] : []);

        $ramIds = is_array($product->ram_option_ids) && count($product->ram_option_ids)
            ? $product->ram_option_ids
            : ($product->ram_option_id ? [(int) $product->ram_option_id] : []);

        return Inertia::render('Products/CreateUpdate', [
            'mode' => 'edit',
            'product' => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'brand_id' => $product->brand_id,
                'sku' => $product->sku,
                'model' => $product->model,
                'device_status' => $product->device_status,
                'price_lkr' => (float) $product->price_lkr,

                'discount_type' => $product->discount_type,
                'discount_value' => $product->discount_value,

                'in_stock' => (bool) $product->in_stock,
                'stock_count' => $product->stock_count,
                'low_stock_alert_quantity' => $product->low_stock_alert_quantity,

                'status' => $product->status,
                'is_featured' => (bool) $product->is_featured,
                'is_best_seller' => (bool) $product->is_best_seller,
                'is_top_rated' => (bool) $product->is_top_rated,
                'is_pre_order' => (bool) $product->is_pre_order,
                'is_deal_of_the_day' => (bool) $product->is_deal_of_the_day,
                'is_coming_soon' => (bool) $product->is_coming_soon,

                'warranty_option_id' => $product->warranty_option_id,
                'warranty_period' => $product->warranty_period,

                'os' => $product->os,

                'storage_option_ids' => array_values(array_map('intval', $storageIds)),
                'ram_option_ids' => array_values(array_map('intval', $ramIds)),

                'storage_option_id' => $product->storage_option_id,
                'ram_option_id' => $product->ram_option_id,

                'display_size' => $product->display_size,
                'display_type' => $product->display_type,
                'resolution' => $product->resolution,
                'rear_camera' => $product->rear_camera,
                'front_camera' => $product->front_camera,
                'connectivity' => $product->connectivity,
                'battery_mah' => $product->battery_mah,

                'short_description' => $product->short_description,
                'long_description' => $product->long_description,

                'product_video_url' => $product->product_video_url,

                'color_ids' => collect(
                    !empty($product->color_ids)
                        ? $product->color_ids
                        : $product->colors->pluck('id')->all()
                )->map(fn ($v) => (int) $v)->values()->all(),

                'variants' => $product->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'storage_option_id' => (int) $variant->storage_option_id,
                    'color_option_id' => (int) $variant->color_option_id,
                    'price_lkr' => (float) $variant->price_lkr,
                    'stock_count' => $variant->stock_count,
                    'sku' => $variant->sku,
                    'status' => $variant->status,
                ])->values()->all(),

                'main_image_url' => $product->main_image_url,
                'hover_image_url' => $product->hover_image_url,
                'gallery_urls' => $product->gallery_urls,
                'gallery_paths' => array_values($product->gallery_image_paths ?: []),
            ],
            'categories' => Category::select('id', 'name')->orderBy('name')->get(),
            'brands' => $brands,
            'colors' => ColorOption::select('id', 'name', 'color_code')
                ->orderBy('name')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'color_code' => $c->color_code,
                    'image_url' => $c->image_url,
                ]),
            'warranties' => WarrantyOption::select('id', 'name')->orderBy('name')->get(),
            'storages' => StorageOption::orderBy('value')->get()->map(fn ($s) => [
                'id' => $s->id,
                'label' => trim($s->value . ' ' . $s->unit),
            ]),
            'rams' => RamOption::orderBy('value')->get()->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim($r->value . ' ' . ($r->unit ?? 'GB')),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'color_ids' => $this->normalizeIdArray($request->input('color_ids', [])),
            'storage_option_ids' => $this->normalizeIdArray($request->input('storage_option_ids', [])),
            'ram_option_ids' => $this->normalizeIdArray($request->input('ram_option_ids', [])),
            'variants' => $this->normalizeVariants($request->input('variants', [])),
        ]);

        $validated = $this->validateProduct($request);
        $validated['sku'] = $this->resolveUniqueSku($validated);

        $mainPath = null;
        if ($request->hasFile('main_image')) {
            $mainPath = $request->file('main_image')->store('products', 'public');
        }

        $hoverPath = null;
        if ($request->hasFile('hover_image')) {
            $hoverPath = $request->file('hover_image')->store('products/hover', 'public');
        }

        $galleryPaths = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $img) {
                $galleryPaths[] = $img->store('products/gallery', 'public');
            }
        }

        DB::transaction(function () use ($validated, $mainPath, $hoverPath, $galleryPaths) {
            $product = Product::create([
                ...$validated,
                'slug' => $this->resolveUniqueSlug($validated),
                'color_ids' => $validated['color_ids'] ?? [],
                'main_image_path' => $mainPath,
                'hover_image_path' => $hoverPath,
                'gallery_image_paths' => $galleryPaths ?: null,
                'stock_count' => ($validated['in_stock'] ?? true) ? ($validated['stock_count'] ?? null) : null,
            ]);

            $this->syncVariants($product, $validated['variants'] ?? []);
        });

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function update(Request $request, Product $product)
    {
        $request->merge([
            'color_ids' => $this->normalizeIdArray($request->input('color_ids', [])),
            'storage_option_ids' => $this->normalizeIdArray($request->input('storage_option_ids', [])),
            'ram_option_ids' => $this->normalizeIdArray($request->input('ram_option_ids', [])),
            'variants' => $this->normalizeVariants($request->input('variants', [])),
        ]);

        $validated = $this->validateProduct($request, $product->id);
        $validated['sku'] = $this->resolveUniqueSku($validated, $product->id);

        if ($request->hasFile('main_image')) {
            if ($product->main_image_path && Storage::disk('public')->exists($product->main_image_path)) {
                Storage::disk('public')->delete($product->main_image_path);
            }
            $product->main_image_path = $request->file('main_image')->store('products', 'public');
        }

        if ($request->hasFile('hover_image')) {
            if ($product->hover_image_path && Storage::disk('public')->exists($product->hover_image_path)) {
                Storage::disk('public')->delete($product->hover_image_path);
            }
            $product->hover_image_path = $request->file('hover_image')->store('products/hover', 'public');
        }

        $clearGallery = (bool) $request->input('clear_gallery', false);
        $removeGalleryPaths = array_values(array_filter((array) $request->input('gallery_remove_paths', [])));
        $galleryPaths = array_values(array_filter($product->gallery_image_paths ?: []));

        if ($clearGallery) {
            foreach ($galleryPaths as $p) {
                if ($p && Storage::disk('public')->exists($p)) {
                    Storage::disk('public')->delete($p);
                }
            }
            $galleryPaths = [];
        } elseif (count($removeGalleryPaths)) {
            $toRemove = array_values(array_intersect($galleryPaths, $removeGalleryPaths));

            foreach ($toRemove as $p) {
                if ($p && Storage::disk('public')->exists($p)) {
                    Storage::disk('public')->delete($p);
                }
            }

            $galleryPaths = array_values(array_diff($galleryPaths, $toRemove));
        }

        if ($request->hasFile('gallery_images')) {
            $newPaths = [];
            foreach ($request->file('gallery_images') as $img) {
                $newPaths[] = $img->store('products/gallery', 'public');
            }

            $galleryPaths = $clearGallery
                ? $newPaths
                : array_values(array_merge($galleryPaths, $newPaths));
        }

        $product->gallery_image_paths = count($galleryPaths) ? $galleryPaths : null;

        DB::transaction(function () use ($product, $validated) {
            $product->fill([
                ...$validated,
                'slug' => $product->slug ?: $this->resolveUniqueSlug($validated, $product->id),
                'color_ids' => $validated['color_ids'] ?? [],
                'stock_count' => ($validated['in_stock'] ?? true) ? ($validated['stock_count'] ?? null) : null,
            ]);

            $product->save();

            $this->syncVariants($product, $validated['variants'] ?? []);
        });

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->main_image_path && Storage::disk('public')->exists($product->main_image_path)) {
            Storage::disk('public')->delete($product->main_image_path);
        }

        if ($product->hover_image_path && Storage::disk('public')->exists($product->hover_image_path)) {
            Storage::disk('public')->delete($product->hover_image_path);
        }

        foreach (($product->gallery_image_paths ?: []) as $p) {
            if ($p && Storage::disk('public')->exists($p)) {
                Storage::disk('public')->delete($p);
            }
        }

        $product->load('reviews');
        foreach ($product->reviews as $review) {
            foreach (($review->image_paths ?? []) as $path) {
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }
        $product->reviews()->delete();

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    public function toggleStatus(Product $product)
    {
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return back()->with('success', 'Product status updated.');
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $searchValue = $request->input('search.value');

        $categoryId = $request->input('category_id');
        $brandId = $request->input('brand_id');
        $warrantyOptionId = $request->input('warranty_option_id');
        $colorIds = $request->input('color_ids')
            ? array_values(array_filter(array_map('intval', explode(',', $request->input('color_ids')))))
            : null;

        $baseQuery = Product::with(['category:id,name', 'brand:id,name'])
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->select('products.*', 'categories.name as category_name', 'brands.name as brand_name');

        if ($categoryId) {
            $baseQuery->where('category_id', $categoryId);
        }

        if ($brandId) {
            $baseQuery->where('brand_id', $brandId);
        }

        if ($warrantyOptionId) {
            $baseQuery->where('warranty_option_id', $warrantyOptionId);
        }

        if ($colorIds) {
            $baseQuery->where(function ($q) use ($colorIds) {
                foreach ($colorIds as $cid) {
                    $q->orWhereJsonContains('products.color_ids', (int) $cid);
                }
            });
        }

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue) {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('products.model', 'like', "%{$searchValue}%")
                    ->orWhere('products.sku', 'like', "%{$searchValue}%")
                    ->orWhereHas('category', function ($cq) use ($searchValue) {
                        $cq->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('brand', function ($bq) use ($searchValue) {
                        $bq->where('name', 'like', "%{$searchValue}%");
                    });
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = [
            0 => 'products.id',
            1 => 'products.model',
            2 => 'categories.name',
            3 => 'brands.name',
            4 => 'products.price_lkr',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'products.id';
        $baseQuery->orderBy($orderBy, $orderDir);

        $rows = $baseQuery
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (Product $p) {
            if (!$p->in_stock) {
                $stockBadge = '<span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">Out of Stock</span>';
            } elseif (
                $p->low_stock_alert_quantity !== null &&
                $p->stock_count !== null &&
                $p->stock_count <= $p->low_stock_alert_quantity
            ) {
                $stockBadge = '<span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">Low Stock</span>';
            } else {
                $stockBadge = '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">In Stock</span>';
            }

            $statusBadge = $p->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';
            $toggleLabel = $p->status === 'active' ? 'Deactivate' : 'Activate';
            $toggleClass = $p->status === 'active'
                ? 'border-amber-200 text-amber-700 hover:bg-amber-50'
                : 'border-green-200 text-green-700 hover:bg-green-50';

            $actions = '
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  data-action="toggle"
                  data-id="' . $p->id . '"
                  data-name="' . htmlspecialchars($p->model, ENT_QUOTES, 'UTF-8') . '"
                  class="rounded-full border px-3 py-1.5 text-xs font-medium ' . $toggleClass . '"
                >
                  ' . $toggleLabel . '
                </button>
                <button
                  type="button"
                  data-action="edit"
                  data-id="' . $p->id . '"
                  data-name="' . htmlspecialchars($p->model, ENT_QUOTES, 'UTF-8') . '"
                  class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100"
                >
                  Edit
                </button>
                <button
                  type="button"
                  data-action="delete"
                  data-id="' . $p->id . '"
                  data-name="' . htmlspecialchars($p->model, ENT_QUOTES, 'UTF-8') . '"
                  class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                >
                  Delete
                </button>
              </div>
            ';

            return [
                'id' => $p->id,
                'model' => $p->model,
                'category_name' => $p->category_name ?? '',
                'brand_name' => $p->brand_name ?? '',
                'price_lkr' => number_format($p->price_lkr, 2),
                'stock_badge' => $stockBadge,
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

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'device_status' => ['required', 'in:used,brandnew'],
            'price_lkr' => ['required', 'numeric', 'min:0'],

            'discount_type' => ['nullable', 'in:percent,price'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],

            'in_stock' => ['nullable', 'boolean'],
            'stock_count' => ['nullable', 'integer', 'min:0'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],

            'status' => ['nullable', 'in:active,inactive'],
            'is_featured' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'is_top_rated' => ['nullable', 'boolean'],
            'is_pre_order' => ['nullable', 'boolean'],
            'is_deal_of_the_day' => ['nullable', 'boolean'],
            'is_coming_soon' => ['nullable', 'boolean'],

            'warranty_option_id' => ['nullable', 'exists:warranty_options,id'],
            'warranty_period' => ['nullable', 'string', 'max:100'],

            'os' => ['nullable', 'string', 'max:255'],

            'storage_option_ids' => ['nullable', 'array'],
            'storage_option_ids.*' => ['integer', 'exists:storage_options,id'],

            'ram_option_ids' => ['nullable', 'array'],
            'ram_option_ids.*' => ['integer', 'exists:ram_options,id'],

            'display_size' => ['nullable', 'string', 'max:255'],
            'display_type' => ['nullable', 'string', 'max:255'],
            'resolution' => ['nullable', 'string', 'max:255'],
            'rear_camera' => ['nullable', 'string', 'max:255'],
            'front_camera' => ['nullable', 'string', 'max:255'],
            'connectivity' => ['nullable', 'string', 'max:255'],
            'battery_mah' => ['nullable', 'integer', 'min:0'],

            'short_description' => ['nullable', 'string'],
            'long_description' => ['nullable', 'string'],

            'product_video_url' => ['nullable', 'url', 'max:2048'],

            'color_ids' => ['nullable', 'array'],
            'color_ids.*' => ['integer', 'exists:color_options,id'],

            'variants' => ['nullable', 'array'],
            'variants.*.storage_option_id' => ['required', 'integer', 'exists:storage_options,id'],
            'variants.*.color_option_id' => ['required', 'integer', 'exists:color_options,id'],
            'variants.*.price_lkr' => ['required', 'numeric', 'min:0'],
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
                    'storage_option_id' => isset($row['storage_option_id']) && $row['storage_option_id'] !== ''
                        ? (int) $row['storage_option_id']
                        : null,

                    'color_option_id' => isset($row['color_option_id']) && $row['color_option_id'] !== ''
                        ? (int) $row['color_option_id']
                        : null,

                    'price_lkr' => isset($row['price_lkr']) && $row['price_lkr'] !== ''
                        ? (float) $row['price_lkr']
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
            ->filter(fn ($row) => $row['storage_option_id'] && $row['color_option_id'])
            ->unique(fn ($row) => $row['storage_option_id'] . '-' . $row['color_option_id'])
            ->values()
            ->all();
    }

    private function syncVariants(Product $product, array $variants): void
    {
        $allowedStorageIds = collect($product->storage_option_ids ?? [])->map(fn ($id) => (int) $id)->all();
        $allowedColorIds = collect($product->color_ids ?? [])->map(fn ($id) => (int) $id)->all();

        $rows = collect($variants)
            ->filter(function ($row) use ($allowedStorageIds, $allowedColorIds) {
                return in_array((int) $row['storage_option_id'], $allowedStorageIds, true)
                    && in_array((int) $row['color_option_id'], $allowedColorIds, true);
            })
            ->map(function ($row) {
                return [
                    'storage_option_id' => (int) $row['storage_option_id'],
                    'color_option_id' => (int) $row['color_option_id'],
                    'price_lkr' => (float) $row['price_lkr'],
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

    private function resolveUniqueSku(array $validated, ?int $ignoreId = null): string
    {
        $rawSku = trim((string) ($validated['sku'] ?? ''));

        if ($rawSku === '') {
            $brandName = Brand::query()->whereKey($validated['brand_id'] ?? null)->value('name') ?? 'PRODUCT';
            $model = $validated['model'] ?? 'ITEM';
            $rawSku = $brandName . ' ' . $model . ' ' . now()->format('YmdHis');
        }

        $base = Str::upper(Str::slug($rawSku, '-'));
        $base = $base !== '' ? substr($base, 0, 180) : 'SKU-' . now()->format('YmdHis');

        $candidate = $base;
        $counter = 1;

        while (
            Product::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('sku', $candidate)
                ->exists()
        ) {
            $candidate = substr($base, 0, 170) . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function resolveUniqueSlug(array $validated, ?int $ignoreId = null): string
    {
        $brandName = Brand::query()->whereKey($validated['brand_id'] ?? null)->value('name');
        $source = trim(implode(' ', array_filter([
            $brandName,
            $validated['model'] ?? null,
        ])));

        $base = Str::slug($source);
        $base = $base !== '' ? substr($base, 0, 180) : 'product-' . now()->format('YmdHis');

        $candidate = $base;
        $counter = 2;

        while (
            Product::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = substr($base, 0, 170) . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }


    public function suggestions(Request $request)
{
    $query = trim((string) $request->input('q', ''));

    if ($query === '') {
        return response()->json([]);
    }

    $needle = mb_strtolower($query);

    $items = Product::query()
        ->select(['id', 'model', 'main_image_path', 'status'])
        ->where('status', 'active')
        ->whereNotNull('model')
        ->whereRaw('LOWER(model) LIKE ?', ['%' . $needle . '%'])
        ->orderByRaw(
            "CASE
                WHEN LOWER(model) = ? THEN 0
                WHEN LOWER(model) LIKE ? THEN 1
                WHEN LOWER(model) LIKE ? THEN 2
                ELSE 3
            END",
            [
                $needle,
                $needle . '%',
                '%' . $needle . '%',
            ]
        )
        ->orderBy('model')
        ->limit(20)
        ->get()
        ->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => trim((string) $product->model),
            'image_url' => $product->main_image_url,
        ])
        ->filter(fn (array $item) => $item['name'] !== '')
        ->unique(fn (array $item) => mb_strtolower($item['name']))
        ->values()
        ->take(10);

    return response()->json($items);
}
}
