<?php

namespace App\Http\Controllers\Admin\Motorcycles;

use App\Http\Controllers\Controller;
use App\Models\MotorcycleBikeBrand;
use App\Models\MotorcycleBikeModel;
use App\Models\MotorcycleHelmetBrand;
use App\Models\MotorcycleProduct;
use App\Models\MotorcycleProductCategory;
use App\Models\MotorcycleProductOption;
use App\Models\WarrantyOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MotorcycleProductController extends Controller
{
    public const PRODUCT_TYPE_LABELS = [
        'helmet' => 'Helmet',
        'helmet_accessory' => 'Helmet Accessory',
        'bike_accessory' => 'Bike Accessory',
        'spare_part' => 'Spare Part',
    ];

    public function index(Request $request)
    {
        return Inertia::render('Motorcycles/Products/index', [
            'activeType' => $this->normalizeProductType($request->query('type')),
            'productTypes' => $this->productTypeOptions(),
        ]);
    }

    public function create(Request $request)
    {
        $type = $this->normalizeProductType($request->query('type')) ?: 'helmet';

        return Inertia::render('Motorcycles/Products/partials/CreateUpdate', array_merge($this->formOptions(), [
            'mode' => 'create',
            'product' => [
                'product_type' => $type,
                'status' => 'active',
                'stock_status' => 'in_stock',
                'featured' => false,
                'today_best_deals' => false,
                'best_seller' => false,
                'warranty_option_id' => null,
                'warranty_period' => '',
                'specifications' => [],
            ],
        ]));
    }

    public function edit(MotorcycleProduct $product)
    {
        $product->load([
            'category:id,name',
            'helmetBrand:id,name',
            'compatibleHelmetBrand:id,name',
            'compatibleBikeBrand:id,name',
            'compatibleBikeModel:id,name,bike_brand_id,start_year,end_year,engine_cc',
            'warrantyOption:id,name',
        ]);

        return Inertia::render('Motorcycles/Products/partials/CreateUpdate', array_merge($this->formOptions(), [
            'mode' => 'edit',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'product_type' => $product->product_type,
                'category_id' => $product->category_id,
                'helmet_brand_id' => $product->helmet_brand_id,
                'compatible_helmet_brand_id' => $product->compatible_helmet_brand_id,
                'compatible_bike_brand_id' => $product->compatible_bike_brand_id,
                'compatible_bike_model_id' => $product->compatible_bike_model_id,
                'short_description' => $product->short_description,
                'full_description' => $product->full_description,
                'product_video_url' => $product->product_video_url,
                'main_image_url' => $product->main_image_url,
                'hover_image_url' => $product->hover_image_url,
                'gallery_urls' => $product->gallery_urls,
                'gallery_paths' => array_values($product->gallery_image_paths ?? []),
                'regular_price' => $product->regular_price,
                'sale_price' => $product->sale_price,
                'cost_price' => $product->cost_price,
                'sku' => $product->sku,
                'stock_quantity' => $product->stock_quantity,
                'low_stock_alert_quantity' => $product->low_stock_alert_quantity,
                'stock_status' => $product->stock_status,
                'status' => $product->status,
                'featured' => (bool) $product->featured,
                'today_best_deals' => (bool) $product->today_best_deals,
                'best_seller' => (bool) $product->best_seller,
                'warranty_option_id' => $product->warranty_option_id,
                'warranty_period' => $product->warranty_period,
                'warranty' => $product->warranty,
                'specifications' => $product->specifications ?? [],
            ],
        ]));
    }

    public function data(Request $request)
    {
        $type = $this->normalizeProductType($request->query('type'));
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = MotorcycleProduct::query()
            ->with([
                'category:id,name',
                'helmetBrand:id,name',
                'compatibleHelmetBrand:id,name',
                'compatibleBikeBrand:id,name',
                'compatibleBikeModel:id,name,bike_brand_id,start_year,end_year,engine_cc',
            ])
            ->when($type, fn ($query) => $query->where('product_type', $type));

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('sku', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhere('stock_status', 'like', "%{$searchValue}%")
                    ->orWhereHas('category', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    ->orWhereHas('helmetBrand', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    ->orWhereHas('compatibleBikeBrand', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"))
                    ->orWhereHas('compatibleBikeModel', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"));
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();
        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'product_type',
            5 => 'regular_price',
            6 => 'stock_quantity',
            7 => 'status',
        ];
        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (MotorcycleProduct $product) {
            $productHtml = $product->main_image_url
                ? '<div class="flex items-center gap-3"><img src="' . e($product->main_image_url) . '" alt="' . e($product->name) . '" class="h-12 w-12 rounded-xl border border-neutral-200 object-cover" /><div><div class="font-medium text-neutral-800">' . e($product->name) . '</div><div class="text-xs text-neutral-500">' . e($product->sku ?: '-') . '</div></div></div>'
                : '<div><div class="font-medium text-neutral-800">' . e($product->name) . '</div><div class="text-xs text-neutral-500">' . e($product->sku ?: '-') . '</div></div>';

            $compatibility = $this->compatibilitySummary($product);
            $details = '<div class="text-sm"><div><span class="font-medium">Category:</span> ' . e($product->category?->name ?? '-') . '</div>' . $compatibility . '</div>';
            $price = $product->sale_price ?: $product->regular_price;
            $payload = e(json_encode([
                'id' => $product->id,
                'name' => $product->name,
                'status' => $product->status,
            ], JSON_UNESCAPED_UNICODE));
            $toggleLabel = $product->status === 'active' ? 'Deactivate' : 'Activate';
            $toggleClass = $product->status === 'active'
                ? 'border-amber-200 text-amber-700 hover:bg-amber-50'
                : 'border-green-200 text-green-700 hover:bg-green-50';

            $statusBadge = $product->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $stockStatus = ucwords(str_replace('_', ' ', (string) $product->stock_status));

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
                'type' => e(self::PRODUCT_TYPE_LABELS[$product->product_type] ?? $product->product_type),
                'details' => $details,
                'stock_status' => e($stockStatus),
                'price_display' => $price !== null ? 'LKR ' . number_format((float) $price, 2) : '-',
                'stock_display' => (string) ($product->stock_quantity ?? 0),
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
        $payload = $this->buildPayload($request, $validated);

        MotorcycleProduct::create($payload);

        return redirect()
            ->route('admin.motorcycles.products.index', ['type' => $validated['product_type']])
            ->with('success', 'Motorcycle product created.');
    }

    public function update(Request $request, MotorcycleProduct $product)
    {
        $validated = $this->validateProduct($request, $product);
        $payload = $this->buildPayload($request, $validated, $product);

        $product->update($payload);

        return redirect()
            ->route('admin.motorcycles.products.index', ['type' => $validated['product_type']])
            ->with('success', 'Motorcycle product updated.');
    }

    public function destroy(MotorcycleProduct $product)
    {
        $this->deleteFile($product->main_image_path);
        $this->deleteFile($product->hover_image_path);
        $this->deleteFiles($product->gallery_image_paths ?? []);
        $product->delete();

        return redirect()
            ->route('admin.motorcycles.products.index')
            ->with('success', 'Motorcycle product deleted.');
    }

    public function toggleStatus(MotorcycleProduct $product)
    {
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return back()->with('success', 'Product status updated.');
    }

    protected function validateProduct(Request $request, ?MotorcycleProduct $product = null): array
    {
        $type = $request->input('product_type');

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', Rule::in(MotorcycleProduct::TYPES)],
            'category_id' => ['required', 'integer', 'exists:motorcycle_product_categories,id'],
            'helmet_brand_id' => [$type === 'helmet' ? 'required' : 'nullable', 'integer', 'exists:motorcycle_helmet_brands,id'],
            'compatible_helmet_brand_id' => [$type === 'helmet_accessory' ? 'required' : 'nullable', 'integer', 'exists:motorcycle_helmet_brands,id'],
            'compatible_bike_brand_id' => [in_array($type, ['bike_accessory', 'spare_part'], true) ? 'required' : 'nullable', 'integer', 'exists:motorcycle_bike_brands,id'],
            'compatible_bike_model_id' => [in_array($type, ['bike_accessory', 'spare_part'], true) ? 'required' : 'nullable', 'integer', 'exists:motorcycle_bike_models,id'],
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
            'regular_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:regular_price'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('motorcycle_products', 'sku')->ignore($product?->id)],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
            'stock_status' => ['required', Rule::in(['in_stock', 'out_of_stock', 'pre_order', 'discontinued'])],
            'status' => ['required', 'in:active,inactive'],
            'featured' => ['nullable', 'boolean'],
            'today_best_deals' => ['nullable', 'boolean'],
            'best_seller' => ['nullable', 'boolean'],
            'warranty' => ['nullable', 'string', 'max:255'],
            'warranty_option_id' => ['nullable', 'integer', 'exists:warranty_options,id'],
            'warranty_period' => ['nullable', 'string', 'max:100'],
            'helmet_type_id' => [$type === 'helmet' ? 'required' : 'nullable', 'integer', 'exists:motorcycle_product_options,id'],
            'helmet_size_id' => ['nullable', 'integer', 'exists:motorcycle_product_options,id'],
            'color_id' => ['nullable', 'integer', 'exists:motorcycle_product_options,id'],
            'shell_material' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'string', 'max:255'],
            'safety_certification' => ['nullable', 'string', 'max:255'],
            'visor_type' => ['nullable', 'string', 'max:255'],
            'chin_strap_type' => ['nullable', 'string', 'max:255'],
            'ventilation' => ['nullable', 'string', 'max:255'],
            'removable_inner_liner' => ['nullable', 'boolean'],
            'washable_padding' => ['nullable', 'boolean'],
            'bluetooth_compatible' => ['nullable', 'boolean'],
            'sun_visor_available' => ['nullable', 'boolean'],
            'pinlock_ready' => ['nullable', 'boolean'],
            'accessory_type_id' => [in_array($type, ['helmet_accessory', 'bike_accessory'], true) ? 'required' : 'nullable', 'integer', 'exists:motorcycle_product_options,id'],
            'compatible_helmet_model' => ['nullable', 'string', 'max:255'],
            'compatible_helmet_size_id' => ['nullable', 'integer', 'exists:motorcycle_product_options,id'],
            'material' => ['nullable', 'string', 'max:255'],
            'installation_type' => ['nullable', 'string', 'max:255'],
            'compatible_start_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'compatible_end_year' => ['nullable', 'integer', 'min:1950', 'max:2100', 'gte:compatible_start_year'],
            'mounting_position' => ['nullable', 'string', 'max:255'],
            'waterproof' => ['nullable', 'boolean'],
            'installation_required' => ['nullable', 'boolean'],
            'universal_fit' => ['nullable', 'boolean'],
            'part_type_id' => [$type === 'spare_part' ? 'required' : 'nullable', 'integer', 'exists:motorcycle_product_options,id'],
            'part_number' => [$type === 'spare_part' ? 'required' : 'nullable', 'string', 'max:255'],
            'oem_number' => ['nullable', 'string', 'max:255'],
            'engine_cc' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'position' => ['nullable', 'string', 'max:255'],
            'condition' => ['nullable', Rule::in(['brand_new', 'used', 'reconditioned'])],
            'origin_type' => ['nullable', Rule::in(['genuine', 'aftermarket'])],
        ]);
    }

    protected function buildPayload(Request $request, array $validated, ?MotorcycleProduct $product = null): array
    {
        $mainImagePath = $product?->main_image_path;
        if ($request->hasFile('main_image')) {
            $this->deleteFile($mainImagePath);
            $mainImagePath = $request->file('main_image')->store('motorcycle/products', 'public');
        }

        $hoverImagePath = $product?->hover_image_path;
        if ($request->hasFile('hover_image')) {
            $this->deleteFile($hoverImagePath);
            $hoverImagePath = $request->file('hover_image')->store('motorcycle/products/hover', 'public');
        }

        $galleryPaths = array_values(array_filter($product?->gallery_image_paths ?? []));
        $clearGallery = $request->boolean('clear_gallery');
        $removeGalleryPaths = array_values(array_filter((array) $request->input('gallery_remove_paths', [])));

        if ($product) {
            if ($clearGallery) {
                $this->deleteFiles($galleryPaths);
                $galleryPaths = [];
            } elseif (count($removeGalleryPaths)) {
                $toRemove = array_values(array_intersect($galleryPaths, $removeGalleryPaths));
                $this->deleteFiles($toRemove);
                $galleryPaths = array_values(array_diff($galleryPaths, $toRemove));
            }
        }

        if ($request->hasFile('gallery_images')) {
            $newPaths = collect($request->file('gallery_images', []))
                ->map(fn ($file) => $file->store('motorcycle/products/gallery', 'public'))
                ->values()
                ->all();

            $galleryPaths = ($product && !$clearGallery)
                ? array_values(array_merge($galleryPaths, $newPaths))
                : $newPaths;
        }

        $type = $validated['product_type'];

        return [
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $product?->id),
            'product_type' => $type,
            'category_id' => $validated['category_id'],
            'helmet_brand_id' => $type === 'helmet' ? ($validated['helmet_brand_id'] ?? null) : null,
            'compatible_helmet_brand_id' => $type === 'helmet_accessory' ? ($validated['compatible_helmet_brand_id'] ?? null) : null,
            'compatible_bike_brand_id' => in_array($type, ['bike_accessory', 'spare_part'], true) ? ($validated['compatible_bike_brand_id'] ?? null) : null,
            'compatible_bike_model_id' => in_array($type, ['bike_accessory', 'spare_part'], true) ? ($validated['compatible_bike_model_id'] ?? null) : null,
            'short_description' => $validated['short_description'] ?? null,
            'full_description' => $validated['full_description'] ?? null,
            'product_video_url' => $validated['product_video_url'] ?? null,
            'main_image_path' => $mainImagePath,
            'hover_image_path' => $hoverImagePath,
            'gallery_image_paths' => $galleryPaths,
            'regular_price' => $validated['regular_price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'low_stock_alert_quantity' => $validated['low_stock_alert_quantity'] ?? null,
            'stock_status' => $validated['stock_status'],
            'status' => $validated['status'],
            'featured' => (bool) ($validated['featured'] ?? false),
            'today_best_deals' => (bool) ($validated['today_best_deals'] ?? false),
            'best_seller' => (bool) ($validated['best_seller'] ?? false),
            'warranty_option_id' => $validated['warranty_option_id'] ?? null,
            'warranty_period' => $validated['warranty_period'] ?? null,
            'warranty' => $this->warrantyLabel($validated),
            'specifications' => $this->specificationsForType($type, $validated),
        ];
    }

    protected function specificationsForType(string $type, array $validated): array
    {
        $common = [
            'color_id' => $validated['color_id'] ?? null,
            'material' => $validated['material'] ?? null,
        ];

        return match ($type) {
            'helmet' => array_merge($common, [
                'helmet_type_id' => $validated['helmet_type_id'] ?? null,
                'helmet_size_id' => $validated['helmet_size_id'] ?? null,
                'shell_material' => $validated['shell_material'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'safety_certification' => $validated['safety_certification'] ?? null,
                'visor_type' => $validated['visor_type'] ?? null,
                'chin_strap_type' => $validated['chin_strap_type'] ?? null,
                'ventilation' => $validated['ventilation'] ?? null,
                'removable_inner_liner' => (bool) ($validated['removable_inner_liner'] ?? false),
                'washable_padding' => (bool) ($validated['washable_padding'] ?? false),
                'bluetooth_compatible' => (bool) ($validated['bluetooth_compatible'] ?? false),
                'sun_visor_available' => (bool) ($validated['sun_visor_available'] ?? false),
                'pinlock_ready' => (bool) ($validated['pinlock_ready'] ?? false),
            ]),
            'helmet_accessory' => array_merge($common, [
                'accessory_type_id' => $validated['accessory_type_id'] ?? null,
                'compatible_helmet_model' => $validated['compatible_helmet_model'] ?? null,
                'compatible_helmet_size_id' => $validated['compatible_helmet_size_id'] ?? null,
                'installation_type' => $validated['installation_type'] ?? null,
            ]),
            'bike_accessory' => array_merge($common, [
                'accessory_type_id' => $validated['accessory_type_id'] ?? null,
                'compatible_start_year' => $validated['compatible_start_year'] ?? null,
                'compatible_end_year' => $validated['compatible_end_year'] ?? null,
                'mounting_position' => $validated['mounting_position'] ?? null,
                'waterproof' => (bool) ($validated['waterproof'] ?? false),
                'installation_required' => (bool) ($validated['installation_required'] ?? false),
                'universal_fit' => (bool) ($validated['universal_fit'] ?? false),
            ]),
            'spare_part' => array_merge($common, [
                'part_type_id' => $validated['part_type_id'] ?? null,
                'part_number' => $validated['part_number'] ?? null,
                'oem_number' => $validated['oem_number'] ?? null,
                'compatible_start_year' => $validated['compatible_start_year'] ?? null,
                'compatible_end_year' => $validated['compatible_end_year'] ?? null,
                'engine_cc' => $validated['engine_cc'] ?? null,
                'position' => $validated['position'] ?? null,
                'condition' => $validated['condition'] ?? null,
                'origin_type' => $validated['origin_type'] ?? null,
            ]),
            default => [],
        };
    }

    protected function formOptions(): array
    {
        $options = MotorcycleProductOption::query()
            ->where('status', 'active')
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type')
            ->map(fn ($items) => $items->map(fn (MotorcycleProductOption $option) => [
                'id' => $option->id,
                'name' => $option->name,
                'value' => $option->id,
                'label' => $option->name,
            ])->values())
            ->toArray();

        $bikeBrands = MotorcycleBikeBrand::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (MotorcycleBikeBrand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'value' => $brand->id,
                'label' => $brand->name,
            ])
            ->values();

        return [
            'productTypes' => $this->productTypeOptions(),
            'categories' => $this->activeOptions(MotorcycleProductCategory::class),
            'helmetBrands' => $this->activeOptions(MotorcycleHelmetBrand::class),
            'bikeBrands' => $bikeBrands,
            'bikeModels' => MotorcycleBikeModel::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'bike_brand_id', 'name', 'start_year', 'end_year', 'engine_cc'])
                ->map(fn (MotorcycleBikeModel $model) => [
                    'id' => $model->id,
                    'bike_brand_id' => $model->bike_brand_id,
                    'name' => $model->name,
                    'value' => $model->id,
                    'label' => $model->name,
                    'start_year' => $model->start_year,
                    'end_year' => $model->end_year,
                    'engine_cc' => $model->engine_cc,
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
            'options' => [
                'helmet_type' => $options['helmet_type'] ?? [],
                'helmet_size' => $options['helmet_size'] ?? [],
                'color' => $options['color'] ?? [],
                'accessory_type' => $options['accessory_type'] ?? [],
                'part_type' => $options['part_type'] ?? [],
            ],
        ];
    }

    protected function activeOptions(string $modelClass)
    {
        return $modelClass::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'value' => $item->id,
                'label' => $item->name,
            ])
            ->values();
    }

    protected function productTypeOptions(): array
    {
        return collect(MotorcycleProduct::TYPES)
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => self::PRODUCT_TYPE_LABELS[$type],
            ])
            ->values()
            ->all();
    }

    protected function normalizeProductType(?string $type): ?string
    {
        return in_array($type, MotorcycleProduct::TYPES, true) ? $type : null;
    }

    protected function compatibilitySummary(MotorcycleProduct $product): string
    {
        return match ($product->product_type) {
            'helmet' => '<div><span class="font-medium">Helmet Brand:</span> ' . e($product->helmetBrand?->name ?? '-') . '</div>',
            'helmet_accessory' => '<div><span class="font-medium">Helmet Brand:</span> ' . e($product->compatibleHelmetBrand?->name ?? '-') . '</div>',
            'bike_accessory', 'spare_part' => '<div><span class="font-medium">Fits:</span> ' . e($product->compatibleBikeBrand?->name ?? '-') . ' / ' . e($product->compatibleBikeModel?->name ?? '-') . '</div>',
            default => '',
        };
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'motorcycle-product';
        $slug = $base;
        $index = 2;

        while (MotorcycleProduct::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$index}";
            $index++;
        }

        return $slug;
    }

    protected function warrantyLabel(array $validated): ?string
    {
        $warrantyName = null;

        if (!empty($validated['warranty_option_id'])) {
            $warrantyName = WarrantyOption::query()
                ->whereKey($validated['warranty_option_id'])
                ->value('name');
        }

        $period = trim((string) ($validated['warranty_period'] ?? ''));

        return trim(implode(' ', array_filter([$warrantyName, $period]))) ?: null;
    }

    protected function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function deleteFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteFile($path);
        }
    }
}
