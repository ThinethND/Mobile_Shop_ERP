<?php

namespace App\Http\Controllers\Admin\Cosmetics;

use App\Http\Controllers\Controller;
use App\Models\CosmeticCountryOfOrigin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CosmeticCountryOfOriginController extends Controller
{
    public function index()
    {
        return Inertia::render('Cosmetics/CountriesOfOrigin/index');
    }

    public function create()
    {
        return Inertia::render('Cosmetics/CountriesOfOrigin/partials/CreateUpdate', [
            'mode' => 'create',
            'country' => null,
        ]);
    }

    public function edit(CosmeticCountryOfOrigin $country)
    {
        return Inertia::render('Cosmetics/CountriesOfOrigin/partials/CreateUpdate', [
            'mode' => 'edit',
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'flag_image_url' => $country->flag_image_url,
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = CosmeticCountryOfOrigin::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('code', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'name', // flag column fallback
            2 => 'name',
            3 => 'code',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (CosmeticCountryOfOrigin $country) {
            $flagHtml = $country->flag_image_url
                ? '<div class="flex items-center">
                        <img src="' . e($country->flag_image_url) . '" alt="' . e($country->name) . '" class="h-12 w-12 rounded-xl border border-neutral-200 bg-neutral-50 object-cover" />
                   </div>'
                : '<div class="flex h-12 w-12 items-center justify-center rounded-xl border border-neutral-200 bg-neutral-50 text-[11px] text-neutral-400">
                        No Flag
                   </div>';

            $payload = e(json_encode([
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'flag_image_url' => $country->flag_image_url,
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
                'id' => $country->id,
                'flag_html' => $flagHtml,
                'name' => e($country->name),
                'code' => e($country->code ?? ''),
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
            'code' => $request->filled('code') ? Str::upper(trim((string) $request->input('code'))) : null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:cosmetic_countries_of_origin,name'],
            'code' => ['nullable', 'string', 'max:10', 'unique:cosmetic_countries_of_origin,code'],
            'flag_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $flagPath = null;

        if ($request->hasFile('flag_image')) {
            $flagPath = $request->file('flag_image')->store('cosmetic-countries', 'public');
        }

        CosmeticCountryOfOrigin::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'flag_image_path' => $flagPath,
        ]);

        return redirect()
            ->route('admin.cosmetics.countries-origin.index')
            ->with('success', 'Country of origin created.');
    }

    public function update(Request $request, CosmeticCountryOfOrigin $country)
    {
        $request->merge([
            'code' => $request->filled('code') ? Str::upper(trim((string) $request->input('code'))) : null,
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cosmetic_countries_of_origin', 'name')->ignore($country->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('cosmetic_countries_of_origin', 'code')->ignore($country->id),
            ],
            'flag_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('flag_image')) {
            if ($country->flag_image_path && Storage::disk('public')->exists($country->flag_image_path)) {
                Storage::disk('public')->delete($country->flag_image_path);
            }

            $country->flag_image_path = $request->file('flag_image')->store('cosmetic-countries', 'public');
        }

        $country->name = $validated['name'];
        $country->code = $validated['code'] ?? null;
        $country->save();

        return redirect()
            ->route('admin.cosmetics.countries-origin.index')
            ->with('success', 'Country of origin updated.');
    }

    public function destroy(CosmeticCountryOfOrigin $country)
    {
        if ($country->flag_image_path && Storage::disk('public')->exists($country->flag_image_path)) {
            Storage::disk('public')->delete($country->flag_image_path);
        }

        $country->delete();

        return redirect()
            ->route('admin.cosmetics.countries-origin.index')
            ->with('success', 'Country of origin deleted.');
    }

    public function options()
    {
        $countries = CosmeticCountryOfOrigin::query()
            ->orderBy('name')
            ->get()
            ->map(fn (CosmeticCountryOfOrigin $country) => [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'flag_image_url' => $country->flag_image_url,
                'value' => $country->id,
                'label' => $country->name,
            ]);

        return response()->json($countries);
    }
}

