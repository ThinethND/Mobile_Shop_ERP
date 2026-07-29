<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Models\ShoeBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ShoeBrandController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Brands/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Brands/partials/CreateUpdate', [
            'mode' => 'create',
            'brand' => null,
        ]);
    }

    public function edit(ShoeBrand $brand)
    {
        return Inertia::render('Shoes/Brands/partials/CreateUpdate', [
            'mode' => 'edit',
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'status' => $brand->status,
                'logo_url' => $brand->logo_url,
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = ShoeBrand::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'name',   // logo column fallback
            2 => 'name',
            3 => 'status',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (ShoeBrand $brand) {
            $logoHtml = $brand->logo_url
                ? '<div class="flex items-center">
                        <img src="' . e($brand->logo_url) . '" alt="' . e($brand->name) . '" class="h-12 w-12 rounded-xl border border-neutral-200 bg-neutral-50 object-cover" />
                   </div>'
                : '<div class="flex h-12 w-12 items-center justify-center rounded-xl border border-neutral-200 bg-neutral-50 text-[11px] text-neutral-400">
                        No Logo
                   </div>';

            $statusBadge = $brand->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $payload = e(json_encode([
                'id' => $brand->id,
                'name' => $brand->name,
                'status' => $brand->status,
                'logo_url' => $brand->logo_url,
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
                'id' => $brand->id,
                'logo_html' => $logoHtml,
                'name' => e($brand->name),
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
            'name' => ['required', 'string', 'max:255', 'unique:shoe_brands,name'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $logoPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('shoe-brands', 'public');
        }

        ShoeBrand::create([
            'name' => $validated['name'],
            'logo_path' => $logoPath,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.shoes.brands.index')
            ->with('success', 'Shoe brand created.');
    }

    public function update(Request $request, ShoeBrand $brand)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shoe_brands', 'name')->ignore($brand->id),
            ],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if ($request->hasFile('logo')) {
            if ($brand->logo_path && Storage::disk('public')->exists($brand->logo_path)) {
                Storage::disk('public')->delete($brand->logo_path);
            }

            $brand->logo_path = $request->file('logo')->store('shoe-brands', 'public');
        }

        $brand->name = $validated['name'];
        $brand->status = $validated['status'];
        $brand->save();

        return redirect()
            ->route('admin.shoes.brands.index')
            ->with('success', 'Shoe brand updated.');
    }

    public function destroy(ShoeBrand $brand)
    {
        if ($brand->logo_path && Storage::disk('public')->exists($brand->logo_path)) {
            Storage::disk('public')->delete($brand->logo_path);
        }

        $brand->delete();

        return redirect()
            ->route('admin.shoes.brands.index')
            ->with('success', 'Shoe brand deleted.');
    }

    public function options()
    {
        $brands = ShoeBrand::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (ShoeBrand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'value' => $brand->id,
                'label' => $brand->name,
            ]);

        return response()->json($brands);
    }
}