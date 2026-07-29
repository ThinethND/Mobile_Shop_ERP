<?php

namespace App\Http\Controllers\Admin\Motorcycles;

use App\Http\Controllers\Controller;
use App\Models\MotorcycleProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MotorcycleProductCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Motorcycles/ProductCategories/index');
    }

    public function create()
    {
        return Inertia::render('Motorcycles/ProductCategories/partials/CreateUpdate', [
            'mode' => 'create',
            'category' => null,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function edit(MotorcycleProductCategory $category)
    {
        return Inertia::render('Motorcycles/ProductCategories/partials/CreateUpdate', [
            'mode' => 'edit',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'parent_category_id' => $category->parent_category_id,
                'description' => $category->description,
                'status' => $category->status,
            ],
            'categories' => $this->categoryOptions($category->id),
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = MotorcycleProductCategory::query()->with('parent:id,name');
        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('description', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhereHas('parent', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"));
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();
        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $columns = [
            0 => 'id',
            1 => 'name',
            3 => 'status',
        ];
        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (MotorcycleProductCategory $category) {
            $payload = e(json_encode([
                'id' => $category->id,
                'name' => $category->name,
            ], JSON_UNESCAPED_UNICODE));

            $statusBadge = $category->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $description = (string) ($category->description ?? '');
            $description = mb_strlen($description) > 90 ? mb_substr($description, 0, 87) . '...' : $description;

            return [
                'id' => $category->id,
                'name' => e($category->name),
                'parent' => e($category->parent?->name ?? '-'),
                'description' => $description !== '' ? e($description) : '<span class="text-neutral-400">-</span>',
                'status_badge' => $statusBadge,
                'actions' => $this->actionsHtml($payload, $category->name),
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
        $validated = $this->validateCategory($request);

        MotorcycleProductCategory::create($validated);

        return redirect()
            ->route('admin.motorcycles.categories.index')
            ->with('success', 'Motorcycle product category created.');
    }

    public function update(Request $request, MotorcycleProductCategory $category)
    {
        $validated = $this->validateCategory($request, $category);
        $category->update($validated);

        return redirect()
            ->route('admin.motorcycles.categories.index')
            ->with('success', 'Motorcycle product category updated.');
    }

    public function destroy(MotorcycleProductCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('admin.motorcycles.categories.index')
            ->with('success', 'Motorcycle product category deleted.');
    }

    public function options()
    {
        return response()->json($this->categoryOptions());
    }

    protected function validateCategory(Request $request, ?MotorcycleProductCategory $category = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('motorcycle_product_categories', 'name')
                    ->ignore($category?->id)
                    ->where(fn ($query) => $query->where('parent_category_id', $request->input('parent_category_id'))),
            ],
            'parent_category_id' => [
                'nullable',
                'integer',
                Rule::exists('motorcycle_product_categories', 'id'),
                Rule::notIn([$category?->id]),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    protected function categoryOptions(?int $exceptId = null)
    {
        return MotorcycleProductCategory::query()
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->orderBy('name')
            ->get(['id', 'name', 'parent_category_id'])
            ->map(fn (MotorcycleProductCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'value' => $category->id,
                'label' => $category->name,
                'parent_category_id' => $category->parent_category_id,
            ])
            ->values();
    }

    protected function actionsHtml(string $payload, string $name): string
    {
        return '
            <div class="flex items-center gap-2">
                <button type="button" data-action="edit" data-payload=\'' . $payload . '\' class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">Edit</button>
                <button type="button" data-action="delete" data-payload=\'' . $payload . '\' class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
            </div>
        ';
    }
}
