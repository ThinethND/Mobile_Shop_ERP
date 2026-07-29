<?php

namespace App\Http\Controllers\Admin\Fashion;

use App\Http\Controllers\Controller;
use App\Models\FashionCategory;
use App\Models\FashionProductType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FashionProductTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Fashion/ProductTypes/index');
    }

    public function create()
    {
        return Inertia::render('Fashion/ProductTypes/partials/CreateUpdate', array_merge($this->formOptions(), [
            'mode' => 'create',
            'productType' => null,
        ]));
    }

    public function edit(FashionProductType $productType)
    {
        $productType->load('category:id,name');

        return Inertia::render('Fashion/ProductTypes/partials/CreateUpdate', array_merge($this->formOptions(), [
            'mode' => 'edit',
            'productType' => [
                'id' => $productType->id,
                'category_id' => $productType->category_id,
                'name' => $productType->name,
                'description' => $productType->description,
                'status' => $productType->status,
            ],
        ]));
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = FashionProductType::query()
            ->with('category:id,name')
            ->withCount('products');
        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('description', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhereHas('category', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"));
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();
        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $columns = [
            0 => 'id',
            1 => 'category_id',
            2 => 'name',
            5 => 'status',
        ];
        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (FashionProductType $productType) {
            $payload = e(json_encode([
                'id' => $productType->id,
                'name' => $productType->name,
            ], JSON_UNESCAPED_UNICODE));

            $statusBadge = $productType->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $description = (string) ($productType->description ?? '');
            $description = mb_strlen($description) > 90 ? mb_substr($description, 0, 87) . '...' : $description;

            return [
                'id' => $productType->id,
                'category_name' => e($productType->category?->name ?? 'General'),
                'name' => e($productType->name),
                'products_count' => (string) $productType->products_count,
                'description' => $description !== '' ? e($description) : '<span class="text-neutral-400">-</span>',
                'status_badge' => $statusBadge,
                'actions' => $this->actionsHtml($payload),
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
        FashionProductType::create($this->validateProductType($request));

        return redirect()
            ->route('admin.fashion.product-types.index')
            ->with('success', 'Fashion product type created.');
    }

    public function update(Request $request, FashionProductType $productType)
    {
        $productType->update($this->validateProductType($request, $productType));

        return redirect()
            ->route('admin.fashion.product-types.index')
            ->with('success', 'Fashion product type updated.');
    }

    public function destroy(FashionProductType $productType)
    {
        $productType->delete();

        return redirect()
            ->route('admin.fashion.product-types.index')
            ->with('success', 'Fashion product type deleted.');
    }

    public function options(Request $request)
    {
        $categoryId = $request->integer('category_id');

        $types = FashionProductType::query()
            ->select(['id', 'name', 'category_id'])
            ->where('status', 'active')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderBy('name')
            ->get()
            ->map(fn (FashionProductType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'category_id' => $type->category_id,
                'value' => $type->id,
                'label' => $type->name,
            ])
            ->values();

        return response()->json($types);
    }

    protected function validateProductType(Request $request, ?FashionProductType $productType = null): array
    {
        $categoryId = $request->input('category_id') ?: null;

        return $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:fashion_categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fashion_product_types', 'name')
                    ->ignore($productType?->id)
                    ->where(fn ($query) => $categoryId
                        ? $query->where('category_id', $categoryId)
                        : $query->whereNull('category_id')),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,inactive'],
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
        ];
    }

    protected function actionsHtml(string $payload): string
    {
        return '
            <div class="flex items-center gap-2">
                <button type="button" data-action="edit" data-payload=\'' . $payload . '\' class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">Edit</button>
                <button type="button" data-action="delete" data-payload=\'' . $payload . '\' class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
            </div>
        ';
    }
}
