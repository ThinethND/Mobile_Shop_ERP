<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Models\ShoeType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ShoeTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Types/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Types/partials/CreateUpdate', [
            'mode' => 'create',
            'type' => null,
        ]);
    }

    public function edit(ShoeType $type)
    {
        return Inertia::render('Shoes/Types/partials/CreateUpdate', [
            'mode' => 'edit',
            'type' => [
                'id' => $type->id,
                'name' => $type->name,
                'status' => $type->status,
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = ShoeType::query();

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
            1 => 'name',
            2 => 'status',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (ShoeType $type) {
            $statusBadge = $type->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $payload = e(json_encode([
                'id' => $type->id,
                'name' => $type->name,
                'status' => $type->status,
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
                'id' => $type->id,
                'name' => e($type->name),
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
            'name' => ['required', 'string', 'max:255', 'unique:shoe_types,name'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        ShoeType::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.shoes.types.index')
            ->with('success', 'Shoe type created.');
    }

    public function update(Request $request, ShoeType $type)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shoe_types', 'name')->ignore($type->id),
            ],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $type->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.shoes.types.index')
            ->with('success', 'Shoe type updated.');
    }

    public function destroy(ShoeType $type)
    {
        $type->delete();

        return redirect()
            ->route('admin.shoes.types.index')
            ->with('success', 'Shoe type deleted.');
    }

    public function options()
    {
        $types = ShoeType::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (ShoeType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'value' => $type->id,
                'label' => $type->name,
            ]);

        return response()->json($types);
    }
}