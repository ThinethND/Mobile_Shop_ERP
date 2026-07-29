<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Models\ShoeColor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ShoeColorController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Colors/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Colors/partials/CreateUpdate', [
            'mode' => 'create',
            'color' => null,
        ]);
    }

    public function edit(ShoeColor $color)
    {
        return Inertia::render('Shoes/Colors/partials/CreateUpdate', [
            'mode' => 'edit',
            'color' => [
                'id' => $color->id,
                'name' => $color->name,
                'hex_code' => $color->hex_code,
                'status' => $color->status,
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = ShoeColor::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('hex_code', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'hex_code',
            3 => 'status',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (ShoeColor $color) {
            $statusBadge = $color->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $swatch = $color->hex_code
                ? '<div class="flex items-center gap-2">
                        <span class="inline-block h-5 w-5 rounded-full border border-neutral-300" style="background:' . e($color->hex_code) . ';"></span>
                        <span>' . e($color->hex_code) . '</span>
                   </div>'
                : '<span class="text-neutral-400">-</span>';

            $payload = e(json_encode([
                'id' => $color->id,
                'name' => $color->name,
                'hex_code' => $color->hex_code,
                'status' => $color->status,
            ], JSON_UNESCAPED_UNICODE));

            $actions = '
                <div class="flex items-center gap-2">
                    <button type="button" data-action="edit" data-payload=\'' . $payload . '\' class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">
                        Edit
                    </button>
                    <button type="button" data-action="delete" data-payload=\'' . $payload . '\' class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                        Delete
                    </button>
                </div>
            ';

            return [
                'id' => $color->id,
                'name' => e($color->name),
                'hex_preview' => $swatch,
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
            'name' => ['required', 'string', 'max:255', 'unique:shoe_colors,name'],
            'hex_code' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        ShoeColor::create($validated);

        return redirect()
            ->route('admin.shoes.colors.index')
            ->with('success', 'Shoe color created.');
    }

    public function update(Request $request, ShoeColor $color)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('shoe_colors', 'name')->ignore($color->id)],
            'hex_code' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $color->update($validated);

        return redirect()
            ->route('admin.shoes.colors.index')
            ->with('success', 'Shoe color updated.');
    }

    public function destroy(ShoeColor $color)
    {
        $color->delete();

        return redirect()
            ->route('admin.shoes.colors.index')
            ->with('success', 'Shoe color deleted.');
    }

    public function options()
    {
        $items = ShoeColor::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (ShoeColor $color) => [
                'id' => $color->id,
                'name' => $color->name,
                'hex_code' => $color->hex_code,
                'value' => $color->id,
                'label' => $color->name,
            ]);

        return response()->json($items);
    }
}