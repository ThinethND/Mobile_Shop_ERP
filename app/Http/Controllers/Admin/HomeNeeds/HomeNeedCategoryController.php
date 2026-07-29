<?php

namespace App\Http\Controllers\Admin\HomeNeeds;

use App\Http\Controllers\Controller;
use App\Models\HomeNeedCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class HomeNeedCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('HomeNeeds/Categories/index');
    }

    public function create()
    {
        return Inertia::render('HomeNeeds/Categories/partials/CreateUpdate', [
            'mode' => 'create',
            'category' => null,
        ]);
    }

    public function edit(HomeNeedCategory $category)
    {
        return Inertia::render('HomeNeeds/Categories/partials/CreateUpdate', [
            'mode' => 'edit',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'status' => $category->status,
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = HomeNeedCategory::query()->withCount('products');
        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('description', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
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

        $data = $rows->map(function (HomeNeedCategory $category) {
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
                'products_count' => (string) $category->products_count,
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
        HomeNeedCategory::create($this->validateCategory($request));

        return redirect()
            ->route('admin.home-needs.categories.index')
            ->with('success', 'Home need category created.');
    }

    public function update(Request $request, HomeNeedCategory $category)
    {
        $category->update($this->validateCategory($request, $category));

        return redirect()
            ->route('admin.home-needs.categories.index')
            ->with('success', 'Home need category updated.');
    }

    public function destroy(HomeNeedCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('admin.home-needs.categories.index')
            ->with('success', 'Home need category deleted.');
    }

    public function options()
    {
        return response()->json($this->categoryOptions());
    }

    protected function validateCategory(Request $request, ?HomeNeedCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('home_need_categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    protected function categoryOptions()
    {
        return HomeNeedCategory::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (HomeNeedCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'value' => $category->id,
                'label' => $category->name,
            ])
            ->values();
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
