<?php

namespace App\Http\Controllers\Admin\Cosmetics;

use App\Http\Controllers\Controller;
use App\Models\CosmeticCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CosmeticCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Cosmetics/CosmeticCategories/index');
    }

    public function create()
    {
        return Inertia::render('Cosmetics/CosmeticCategories/partials/CreateUpdate', [
            'mode' => 'create',
            'category' => null,
        ]);
    }

    public function edit(CosmeticCategory $category)
    {
        return Inertia::render('Cosmetics/CosmeticCategories/partials/CreateUpdate', [
            'mode' => 'edit',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
        ]);
    }

    public function generateSlug(Request $request)
    {
        $name = trim((string) $request->input('name', ''));
        $ignoreId = $request->integer('ignore_id') ?: null;

        return response()->json([
            'slug' => $this->makeUniqueSlug($name, $ignoreId),
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = CosmeticCategory::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('slug', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'slug',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (CosmeticCategory $category) {
            $payload = e(json_encode([
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
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
                'id' => $category->id,
                'name' => e($category->name),
                'slug' => e($category->slug),
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
            'slug' => Str::slug((string) $request->input('slug', $request->input('name', ''))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:cosmetic_categories,name'],
            'slug' => ['required', 'string', 'max:255', 'unique:cosmetic_categories,slug'],
        ]);

        CosmeticCategory::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
        ]);

        return redirect()
            ->route('admin.cosmetics.categories.index')
            ->with('success', 'Cosmetic category created.');
    }

    public function update(Request $request, CosmeticCategory $category)
    {
        $request->merge([
            'slug' => Str::slug((string) $request->input('slug', $request->input('name', ''))),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cosmetic_categories', 'name')->ignore($category->id),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cosmetic_categories', 'slug')->ignore($category->id),
            ],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
        ]);

        return redirect()
            ->route('admin.cosmetics.categories.index')
            ->with('success', 'Cosmetic category updated.');
    }

    public function destroy(CosmeticCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('admin.cosmetics.categories.index')
            ->with('success', 'Cosmetic category deleted.');
    }

    public function options()
    {
        $categories = CosmeticCategory::query()
            ->orderBy('name')
            ->get()
            ->map(fn (CosmeticCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'value' => $category->id,
                'label' => $category->name,
            ]);

        return response()->json($categories);
    }

    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? substr($base, 0, 220) : 'cosmetic-category';

        $candidate = $base;
        $counter = 1;

        while (
            CosmeticCategory::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = substr($base, 0, 210) . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}

