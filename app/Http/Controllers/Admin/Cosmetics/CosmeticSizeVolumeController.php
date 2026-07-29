<?php

namespace App\Http\Controllers\Admin\Cosmetics;

use App\Http\Controllers\Controller;
use App\Models\CosmeticSizeVolume;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CosmeticSizeVolumeController extends Controller
{
    private const UNIT_OPTIONS = ['ml', 'g', 'kg', 'l', 'oz'];

    public function index()
    {
        return Inertia::render('Cosmetics/SizesVolume/index');
    }

    public function create()
    {
        return Inertia::render('Cosmetics/SizesVolume/partials/CreateUpdate', [
            'mode' => 'create',
            'sizeVolume' => null,
        ]);
    }

    public function edit(CosmeticSizeVolume $sizeVolume)
    {
        return Inertia::render('Cosmetics/SizesVolume/partials/CreateUpdate', [
            'mode' => 'edit',
            'sizeVolume' => [
                'id' => $sizeVolume->id,
                'size' => $sizeVolume->size,
                'unit' => $sizeVolume->unit,
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = CosmeticSizeVolume::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('size', 'like', "%{$searchValue}%")
                    ->orWhere('unit', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'size',
            2 => 'unit',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (CosmeticSizeVolume $sizeVolume) {
            $payload = e(json_encode([
                'id' => $sizeVolume->id,
                'size' => $sizeVolume->size,
                'unit' => $sizeVolume->unit,
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

            $sizeLabel = rtrim(rtrim((string) $sizeVolume->size, '0'), '.');

            return [
                'id' => $sizeVolume->id,
                'size' => e($sizeLabel),
                'unit' => e($sizeVolume->unit),
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
            'size' => [
                'required',
                'numeric',
                'min:0',
                Rule::unique('cosmetic_size_volumes', 'size')
                    ->where(fn ($query) => $query->where('unit', $request->input('unit'))),
            ],
            'unit' => ['required', Rule::in(self::UNIT_OPTIONS)],
        ]);

        CosmeticSizeVolume::create([
            'size' => $validated['size'],
            'unit' => $validated['unit'],
        ]);

        return redirect()
            ->route('admin.cosmetics.sizes-volume.index')
            ->with('success', 'Size / volume created.');
    }

    public function update(Request $request, CosmeticSizeVolume $sizeVolume)
    {
        $validated = $request->validate([
            'size' => [
                'required',
                'numeric',
                'min:0',
                Rule::unique('cosmetic_size_volumes', 'size')
                    ->ignore($sizeVolume->id)
                    ->where(fn ($query) => $query->where('unit', $request->input('unit'))),
            ],
            'unit' => ['required', Rule::in(self::UNIT_OPTIONS)],
        ]);

        $sizeVolume->update([
            'size' => $validated['size'],
            'unit' => $validated['unit'],
        ]);

        return redirect()
            ->route('admin.cosmetics.sizes-volume.index')
            ->with('success', 'Size / volume updated.');
    }

    public function destroy(CosmeticSizeVolume $sizeVolume)
    {
        $sizeVolume->delete();

        return redirect()
            ->route('admin.cosmetics.sizes-volume.index')
            ->with('success', 'Size / volume deleted.');
    }

    public function options()
    {
        $options = CosmeticSizeVolume::query()
            ->orderBy('size')
            ->orderBy('unit')
            ->get()
            ->map(function (CosmeticSizeVolume $sizeVolume) {
                $sizeLabel = rtrim(rtrim((string) $sizeVolume->size, '0'), '.');
                $label = trim($sizeLabel . $sizeVolume->unit);

                return [
                    'id' => $sizeVolume->id,
                    'name' => $label,
                    'value' => $sizeVolume->id,
                    'label' => $label,
                ];
            });

        return response()->json($options);
    }
}
