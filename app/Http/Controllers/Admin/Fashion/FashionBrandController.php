<?php

namespace App\Http\Controllers\Admin\Fashion;

use App\Http\Controllers\Controller;
use App\Models\FashionBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FashionBrandController extends Controller
{
    public function index()
    {
        return Inertia::render('Fashion/Brands/index');
    }

    public function create()
    {
        return Inertia::render('Fashion/Brands/partials/CreateUpdate', [
            'mode' => 'create',
            'brand' => null,
        ]);
    }

    public function edit(FashionBrand $brand)
    {
        return Inertia::render('Fashion/Brands/partials/CreateUpdate', [
            'mode' => 'edit',
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'description' => $brand->description,
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

        $baseQuery = FashionBrand::query()->withCount('products');
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

        $data = $rows->map(function (FashionBrand $brand) {
            $payload = e(json_encode([
                'id' => $brand->id,
                'name' => $brand->name,
            ], JSON_UNESCAPED_UNICODE));

            $brandHtml = $brand->logo_url
                ? '<div class="flex items-center gap-3"><img src="' . e($brand->logo_url) . '" alt="' . e($brand->name) . '" class="h-10 w-10 rounded-xl border border-neutral-200 object-cover" /><span class="font-medium text-neutral-800">' . e($brand->name) . '</span></div>'
                : '<span class="font-medium text-neutral-800">' . e($brand->name) . '</span>';

            $description = (string) ($brand->description ?? '');
            $description = mb_strlen($description) > 90 ? mb_substr($description, 0, 87) . '...' : $description;

            $statusBadge = $brand->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            return [
                'id' => $brand->id,
                'brand' => $brandHtml,
                'products_count' => (string) $brand->products_count,
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
        $validated = $this->validateBrand($request);
        $logoPath = $request->hasFile('logo')
            ? $request->file('logo')->store('fashion/brands', 'public')
            : null;

        FashionBrand::create([
            ...$validated,
            'logo_path' => $logoPath,
        ]);

        return redirect()
            ->route('admin.fashion.brands.index')
            ->with('success', 'Fashion brand created.');
    }

    public function update(Request $request, FashionBrand $brand)
    {
        $validated = $this->validateBrand($request, $brand);

        if ($request->hasFile('logo')) {
            $this->deleteStoredFile($brand->logo_path);
            $brand->logo_path = $request->file('logo')->store('fashion/brands', 'public');
        }

        $brand->fill($validated);
        $brand->save();

        return redirect()
            ->route('admin.fashion.brands.index')
            ->with('success', 'Fashion brand updated.');
    }

    public function destroy(FashionBrand $brand)
    {
        $this->deleteStoredFile($brand->logo_path);
        $brand->delete();

        return redirect()
            ->route('admin.fashion.brands.index')
            ->with('success', 'Fashion brand deleted.');
    }

    public function options()
    {
        return response()->json($this->brandOptions());
    }

    protected function validateBrand(Request $request, ?FashionBrand $brand = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('fashion_brands', 'name')->ignore($brand?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,inactive'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    protected function brandOptions()
    {
        return FashionBrand::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (FashionBrand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'value' => $brand->id,
                'label' => $brand->name,
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

    protected function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
