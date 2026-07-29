<?php

namespace App\Http\Controllers\Admin\Cosmetics;

use App\Http\Controllers\Controller;
use App\Models\CosmeticProductType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CosmeticProductTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Cosmetics/ProductTypes/index');
    }

    public function create()
    {
        return Inertia::render('Cosmetics/ProductTypes/partials/CreateUpdate', [
            'mode' => 'create',
            'productType' => null,
        ]);
    }

    public function edit(CosmeticProductType $productType)
    {
        return Inertia::render('Cosmetics/ProductTypes/partials/CreateUpdate', [
            'mode' => 'edit',
            'productType' => [
                'id' => $productType->id,
                'cosmetic_category_id' => $productType->cosmetic_category_id,
                'name' => $productType->name,
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = CosmeticProductType::query()->with('category:id,name');

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhereHas('category', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"));
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'cosmetic_category_id',
            2 => 'name',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (CosmeticProductType $productType) {
            $payload = e(json_encode([
                'id' => $productType->id,
                'cosmetic_category_id' => $productType->cosmetic_category_id,
                'name' => $productType->name,
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
                'id' => $productType->id,
                'category_name' => e($productType->category?->name ?? '-'),
                'name' => e($productType->name),
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
        $validated = $request->validate([
            'cosmetic_category_id' => ['required', 'integer', 'exists:cosmetic_categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cosmetic_product_types', 'name')
                    ->where(fn ($query) => $query->where('cosmetic_category_id', $request->input('cosmetic_category_id'))),
            ],
        ]);

        CosmeticProductType::create([
            'cosmetic_category_id' => $validated['cosmetic_category_id'],
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('admin.cosmetics.product-types.index')
            ->with('success', 'Product type created.');
    }

    public function update(Request $request, CosmeticProductType $productType)
    {
        $validated = $request->validate([
            'cosmetic_category_id' => ['required', 'integer', 'exists:cosmetic_categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cosmetic_product_types', 'name')
                    ->ignore($productType->id)
                    ->where(fn ($query) => $query->where('cosmetic_category_id', $request->input('cosmetic_category_id'))),
            ],
        ]);

        $productType->update([
            'cosmetic_category_id' => $validated['cosmetic_category_id'],
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('admin.cosmetics.product-types.index')
            ->with('success', 'Product type updated.');
    }

    public function destroy(CosmeticProductType $productType)
    {
        $productType->delete();

        return redirect()
            ->route('admin.cosmetics.product-types.index')
            ->with('success', 'Product type deleted.');
    }

    public function options(Request $request)
    {
        $categoryId = $request->integer('category_id');

        $types = CosmeticProductType::query()
            ->select(['id', 'name', 'cosmetic_category_id'])
            ->when($categoryId, fn ($q) => $q->where('cosmetic_category_id', $categoryId))
            ->orderBy('name')
            ->get()
            ->map(fn (CosmeticProductType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'cosmetic_category_id' => $type->cosmetic_category_id,
                'value' => $type->id,
                'label' => $type->name,
            ])
            ->values();

        return response()->json($types);
    }
}

