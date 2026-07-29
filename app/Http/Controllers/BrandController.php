<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function index()
    {
        return Inertia::render('Brands/index');
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $searchValue = $request->input('search.value');

        $baseQuery = Brand::query();

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

        $rows = $baseQuery->skip($start)->take($length)->get();

        $data = $rows->map(function (Brand $b) {
            $statusBadge = $b->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $actions = '
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  data-action="edit"
                  data-id="' . $b->id . '"
                  data-name="' . e($b->name) . '"
                  class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100"
                >
                  Edit
                </button>
                <button
                  type="button"
                  data-action="delete"
                  data-id="' . $b->id . '"
                  data-name="' . e($b->name) . '"
                  class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                >
                  Delete
                </button>
              </div>
            ';

            return [
                'id' => $b->id,
                'name' => e($b->name),
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

    public function create()
    {
        return Inertia::render('Brands/partials/CreateUpdate', [
            'mode' => 'create',
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brand' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name'],
            'status' => ['required', 'in:active,inactive'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $logoPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
            'logo_path' => $logoPath,
        ]);

        $brand->categories()->sync($validated['category_ids'] ?? []);

        return redirect()->route('brands.index')->with('success', 'Brand created.');
    }

    public function edit(Brand $brand)
    {
        $brand->load('categories:id');

        return Inertia::render('Brands/partials/CreateUpdate', [
            'mode' => 'edit',
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'status' => $brand->status,
                'logo_url' => $brand->logo_url,
                'category_ids' => $brand->categories
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name,' . $brand->id],
            'status' => ['required', 'in:active,inactive'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        if ($request->hasFile('logo')) {
            if ($brand->logo_path && Storage::disk('public')->exists($brand->logo_path)) {
                Storage::disk('public')->delete($brand->logo_path);
            }

            $brand->logo_path = $request->file('logo')->store('brands', 'public');
        }

        $brand->name = $validated['name'];
        $brand->status = $validated['status'];
        $brand->save();

        $brand->categories()->sync($validated['category_ids'] ?? []);

        return redirect()->route('brands.index')->with('success', 'Brand updated.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->logo_path && Storage::disk('public')->exists($brand->logo_path)) {
            Storage::disk('public')->delete($brand->logo_path);
        }

        $brand->categories()->detach();
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand deleted.');
    }
}