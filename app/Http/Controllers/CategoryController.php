<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Categories/index', [
            'message' => 'Categories',
        ]);
    }

    public function create()
    {
        return Inertia::render('Categories/partials/CreateUpdate', [
            'mode' => 'create',
            'category' => null,
            'brands' => Brand::query()
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(Category $category)
    {
        $category->load('brands:id');

        return Inertia::render('Categories/partials/CreateUpdate', [
            'mode' => 'edit',
            'brands' => Brand::query()
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'status' => $category->status,
                'image_url' => $category->image_url,
                'brand_ids' => $category->brands->pluck('id')->map(fn ($id) => (int) $id)->values(),
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $searchValue = $request->input('search.value');

        $baseQuery = Category::query()->with('brands:id,name');

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue) {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = $request->input('order.0.dir', 'desc');

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'status',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';
        $baseQuery->orderBy($orderBy, $orderDir);

        $rows = $baseQuery
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (Category $c) {
            $statusBadge = $c->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $actions = '
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  data-action="edit"
                  data-id="' . $c->id . '"
                  data-name="' . e($c->name) . '"
                  class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100"
                >
                  Edit
                </button>
                <button
                  type="button"
                  data-action="delete"
                  data-id="' . $c->id . '"
                  data-name="' . e($c->name) . '"
                  class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                >
                  Delete
                </button>
              </div>
            ';

            return [
                'id' => $c->id,
                'name' => e($c->name),
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
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255', 'unique:categories,name'],
            'status' => ['required', 'in:active,inactive'],
            'image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['integer', 'exists:brands,id'],
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
            'image_path' => $imagePath,
        ]);

        $category->brands()->sync($validated['brand_ids'] ?? []);

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
            'status' => ['required', 'in:active,inactive'],
            'image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['integer', 'exists:brands,id'],
        ]);

        if ($request->hasFile('image')) {
            if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
                Storage::disk('public')->delete($category->image_path);
            }

            $category->image_path = $request->file('image')->store('categories', 'public');
        }

        $category->name = $validated['name'];
        $category->status = $validated['status'];
        $category->save();

        $category->brands()->sync($validated['brand_ids'] ?? []);

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->brands()->detach();
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }
}