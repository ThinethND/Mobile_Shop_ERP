<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Models\ShoeSizeType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ShoeSizeTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/SizeTypes/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/SizeTypes/partials/CreateUpdate', [
            'mode' => 'create',
            'sizeType' => null,
        ]);
    }

    public function edit(ShoeSizeType $sizeType)
    {
        return Inertia::render('Shoes/SizeTypes/partials/CreateUpdate', [
            'mode' => 'edit',
            'sizeType' => [
                'id' => $sizeType->id,
                'code' => $sizeType->code,
                'name' => $sizeType->name,
                'status' => $sizeType->status,
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = ShoeSizeType::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('code', 'like', "%{$searchValue}%")
                    ->orWhere('name', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'code',
            2 => 'name',
            3 => 'status',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (ShoeSizeType $sizeType) {
            $statusBadge = $sizeType->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $payload = e(json_encode([
                'id' => $sizeType->id,
                'code' => $sizeType->code,
                'name' => $sizeType->name,
                'status' => $sizeType->status,
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
                'id' => $sizeType->id,
                'code' => e($sizeType->code),
                'name' => e($sizeType->name),
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
            'code' => ['required', 'string', 'max:20', 'unique:shoe_size_types,code'],
            'name' => ['required', 'string', 'max:255', 'unique:shoe_size_types,name'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        ShoeSizeType::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.shoes.size-types.index')
            ->with('success', 'Shoe size type created.');
    }

    public function update(Request $request, ShoeSizeType $sizeType)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('shoe_size_types', 'code')->ignore($sizeType->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('shoe_size_types', 'name')->ignore($sizeType->id)],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $sizeType->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.shoes.size-types.index')
            ->with('success', 'Shoe size type updated.');
    }

    public function destroy(ShoeSizeType $sizeType)
    {
        $sizeType->delete();

        return redirect()
            ->route('admin.shoes.size-types.index')
            ->with('success', 'Shoe size type deleted.');
    }

    public function options()
    {
        $items = ShoeSizeType::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (ShoeSizeType $sizeType) => [
                'id' => $sizeType->id,
                'code' => $sizeType->code,
                'name' => $sizeType->name,
                'value' => $sizeType->id,
                'label' => $sizeType->name,
            ]);

        return response()->json($items);
    }
}