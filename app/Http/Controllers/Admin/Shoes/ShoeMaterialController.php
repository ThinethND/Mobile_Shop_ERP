<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Models\ShoeMaterial;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ShoeMaterialController extends Controller
{
    public function index()
    {


    
        return Inertia::render('Shoes/Materials/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Materials/partials/CreateUpdate', [
            'mode' => 'create',
            'material' => null,
        ]);
    }

    public function edit(ShoeMaterial $material)
    {
        return Inertia::render('Shoes/Materials/partials/CreateUpdate', [
            'mode' => 'edit',
            'material' => [
                'id' => $material->id,
                'name' => $material->name,
                'status' => $material->status,
            ],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = ShoeMaterial::query();

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

        $data = $rows->map(function (ShoeMaterial $material) {
            $statusBadge = $material->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $payload = e(json_encode([
                'id' => $material->id,
                'name' => $material->name,
                'status' => $material->status,
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
                'id' => $material->id,
                'name' => e($material->name),
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
            'name' => ['required', 'string', 'max:255', 'unique:shoe_materials,name'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        ShoeMaterial::create($validated);

        return redirect()
            ->route('admin.shoes.materials.index')
            ->with('success', 'Shoe material created.');
    }

    public function update(Request $request, ShoeMaterial $material)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('shoe_materials', 'name')->ignore($material->id)],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $material->update($validated);

        return redirect()
            ->route('admin.shoes.materials.index')
            ->with('success', 'Shoe material updated.');
    }

    public function destroy(ShoeMaterial $material)
    {
        $material->delete();

        return redirect()
            ->route('admin.shoes.materials.index')
            ->with('success', 'Shoe material deleted.');
    }

    public function options()
    {
        $items = ShoeMaterial::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (ShoeMaterial $material) => [
                'id' => $material->id,
                'name' => $material->name,
                'value' => $material->id,
                'label' => $material->name,
            ]);

        return response()->json($items);
    }
}