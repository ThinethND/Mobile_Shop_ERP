<?php

namespace App\Http\Controllers\Admin\Cosmetics;

use App\Http\Controllers\Controller;
use App\Models\CosmeticBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CosmeticBrandController extends Controller
{
    public function index()
    {
        return Inertia::render('Cosmetics/CosmeticBrands/index');
    }

    public function create()
    {
        return Inertia::render('Cosmetics/CosmeticBrands/partials/CreateUpdate', [
            'mode' => 'create',
            'brand' => null,
        ]);
    }

    public function edit(CosmeticBrand $brand)
    {
        return Inertia::render('Cosmetics/CosmeticBrands/partials/CreateUpdate', [
            'mode' => 'edit',
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'status' => $brand->status,
                'logo_url' => $brand->logo_url,
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

        $baseQuery = CosmeticBrand::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('slug', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'name', // logo column fallback
            2 => 'name',
            3 => 'slug',
            4 => 'status',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (CosmeticBrand $brand) {
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
                'slug' => $brand->slug,
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
                'slug' => e($brand->slug),
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
        $request->merge([
            'slug' => Str::slug((string) $request->input('slug', $request->input('name', ''))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:cosmetic_brands,name'],
            'slug' => ['required', 'string', 'max:255', 'unique:cosmetic_brands,slug'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $logoPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('cosmetic-brands', 'public');
        }

        CosmeticBrand::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'logo_path' => $logoPath,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.cosmetics.brands.index')
            ->with('success', 'Cosmetic brand created.');
    }

    public function update(Request $request, CosmeticBrand $brand)
    {
        $request->merge([
            'slug' => Str::slug((string) $request->input('slug', $request->input('name', ''))),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cosmetic_brands', 'name')->ignore($brand->id),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cosmetic_brands', 'slug')->ignore($brand->id),
            ],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if ($request->hasFile('logo')) {
            if ($brand->logo_path && Storage::disk('public')->exists($brand->logo_path)) {
                Storage::disk('public')->delete($brand->logo_path);
            }

            $brand->logo_path = $request->file('logo')->store('cosmetic-brands', 'public');
        }

        $brand->name = $validated['name'];
        $brand->slug = $validated['slug'];
        $brand->status = $validated['status'];
        $brand->save();

        return redirect()
            ->route('admin.cosmetics.brands.index')
            ->with('success', 'Cosmetic brand updated.');
    }

    public function destroy(CosmeticBrand $brand)
    {
        if ($brand->logo_path && Storage::disk('public')->exists($brand->logo_path)) {
            Storage::disk('public')->delete($brand->logo_path);
        }

        $brand->delete();

        return redirect()
            ->route('admin.cosmetics.brands.index')
            ->with('success', 'Cosmetic brand deleted.');
    }

    public function options()
    {
        $brands = CosmeticBrand::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (CosmeticBrand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'value' => $brand->id,
                'label' => $brand->name,
            ]);

        return response()->json($brands);
    }

    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? substr($base, 0, 220) : 'cosmetic-brand';

        $candidate = $base;
        $counter = 1;

        while (
            CosmeticBrand::query()
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

