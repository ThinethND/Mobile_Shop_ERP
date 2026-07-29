<?php

namespace App\Http\Controllers;

use App\Models\RamOption;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RamOptionController extends Controller
{
    public function index()
    {
        return Inertia::render('Ram/index');
    }

    public function create()
    {
        return Inertia::render('Ram/CreateUpdate', [
            'mode' => 'create',
            'ram' => null,
        ]);
    }

    public function edit(RamOption $ramOption)
    {
        return Inertia::render('Ram/CreateUpdate', [
            'mode' => 'edit',
            'ram' => [
                'id' => $ramOption->id,
                'value' => $ramOption->value,
                'status' => $ramOption->status,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'value' => ['required', 'integer', 'min:1', 'max:1000000', 'unique:ram_options,value'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        RamOption::create($validated);

        return redirect()->route('ram.index')->with('success', 'RAM option created.');
    }

    public function update(Request $request, RamOption $ramOption)
    {
        $validated = $request->validate([
            'value' => ['required', 'integer', 'min:1', 'max:1000000', 'unique:ram_options,value,' . $ramOption->id],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $ramOption->update($validated);

        return redirect()->route('ram.index')->with('success', 'RAM option updated.');
    }

    public function destroy(RamOption $ramOption)
    {
        $ramOption->delete();

        return redirect()->route('ram.index')->with('success', 'RAM option deleted.');
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $searchValue = $request->input('search.value');

        $baseQuery = RamOption::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue) {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('value', 'like', "%{$searchValue}%")
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

        $data = $rows->map(function (RamOption $r) {
            $label = $r->value . ' GB';

            $statusBadge = $r->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $actions = '
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  data-action="edit"
                  data-id="'.$r->id.'"
                  data-label="'.$label.'"
                  class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100"
                >
                  Edit
                </button>
                <button
                  type="button"
                  data-action="delete"
                  data-id="'.$r->id.'"
                  data-label="'.$label.'"
                  class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                >
                  Delete
                </button>
              </div>
            ';

            return [
                'id' => $r->id,
                'ram_label' => $label,
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
