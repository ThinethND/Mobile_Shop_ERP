<?php

namespace App\Http\Controllers;

use App\Models\StorageOption;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StorageOptionController extends Controller
{
    public function index()
    {
        return Inertia::render('Storage/index');
    }

    public function create()
    {
        return Inertia::render('Storage/CreateUpdate', [
            'mode' => 'create',
            'storage' => null,
        ]);
    }

    public function edit(StorageOption $storageOption)
    {
        return Inertia::render('Storage/CreateUpdate', [
            'mode' => 'edit',
            'storage' => [
                'id' => $storageOption->id,
                'value' => $storageOption->value,
                'unit' => $storageOption->unit,
                'status' => $storageOption->status,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'value' => ['required', 'integer', 'min:1', 'max:1000000'],
            'unit' => ['required', 'in:GB,TB'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        StorageOption::create($validated);

        return redirect()->route('storage.index')->with('success', 'Storage option created.');
    }

    public function update(Request $request, StorageOption $storageOption)
    {
        $validated = $request->validate([
            'value' => ['required', 'integer', 'min:1', 'max:1000000'],
            'unit' => ['required', 'in:GB,TB'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // prevent duplicate (value + unit) clashes
        $exists = StorageOption::where('id', '!=', $storageOption->id)
            ->where('value', $validated['value'])
            ->where('unit', $validated['unit'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'value' => 'This storage option already exists.',
            ]);
        }

        $storageOption->update($validated);

        return redirect()->route('storage.index')->with('success', 'Storage option updated.');
    }

    public function destroy(StorageOption $storageOption)
    {
        $storageOption->delete();

        return redirect()->route('storage.index')->with('success', 'Storage option deleted.');
    }

    /**
     * DataTables server-side JSON
     */
    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $searchValue = $request->input('search.value');

        $baseQuery = StorageOption::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue) {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('value', 'like', "%{$searchValue}%")
                  ->orWhere('unit', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = $request->input('order.0.dir', 'desc');

        $columns = [
            0 => 'id',
            1 => 'value',
            2 => 'status',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';
        $baseQuery->orderBy($orderBy, $orderDir);

        $rows = $baseQuery->skip($start)->take($length)->get();

        $data = $rows->map(function (StorageOption $s) {
            $label = $s->value . ' ' . $s->unit;

            $statusBadge = $s->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $actions = '
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  data-action="edit"
                  data-id="'.$s->id.'"
                  data-label="'.$label.'"
                  class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100"
                >
                  Edit
                </button>
                <button
                  type="button"
                  data-action="delete"
                  data-id="'.$s->id.'"
                  data-label="'.$label.'"
                  class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                >
                  Delete
                </button>
              </div>
            ';

            return [
                'id' => $s->id,
                'storage_label' => $label,
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
}
