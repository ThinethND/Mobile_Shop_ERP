<?php

namespace App\Http\Controllers;

use App\Models\WarrantyOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WarrantyOptionController extends Controller
{
    public function index()
    {
        return Inertia::render('Warranty/index');
    }

    public function create()
    {
        return Inertia::render('Warranty/partials/CreateUpdate', [
            'mode' => 'create',
            'warranty' => null,
        ]);
    }

    public function edit(WarrantyOption $warrantyOption)
    {
        return Inertia::render('Warranty/partials/CreateUpdate', [
            'mode' => 'edit',
            'warranty' => [
                'id' => $warrantyOption->id,
                'name' => $warrantyOption->name,
                'status' => $warrantyOption->status,
                'image_url' => $warrantyOption->image_url,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255', 'unique:warranty_options,name'],
            'status' => ['required', 'in:active,inactive'],
            'image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('warranties', 'public');
        }

        WarrantyOption::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
            'image_path' => $path,
        ]);

        return redirect()->route('warranty.index')->with('success', 'Warranty created.');
    }

    public function update(Request $request, WarrantyOption $warrantyOption)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255', 'unique:warranty_options,name,' . $warrantyOption->id],
            'status' => ['required', 'in:active,inactive'],
            'image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($warrantyOption->image_path && Storage::disk('public')->exists($warrantyOption->image_path)) {
                Storage::disk('public')->delete($warrantyOption->image_path);
            }
            $warrantyOption->image_path = $request->file('image')->store('warranties', 'public');
        }

        $warrantyOption->name = $validated['name'];
        $warrantyOption->status = $validated['status'];
        $warrantyOption->save();

        return redirect()->route('warranty.index')->with('success', 'Warranty updated.');
    }

    public function destroy(WarrantyOption $warrantyOption)
    {
        if ($warrantyOption->image_path && Storage::disk('public')->exists($warrantyOption->image_path)) {
            Storage::disk('public')->delete($warrantyOption->image_path);
        }
        $warrantyOption->delete();

        return redirect()->route('warranty.index')->with('success', 'Warranty deleted.');
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = $request->input('search.value');

        $q = WarrantyOption::query();
        $recordsTotal = (clone $q)->count();

        if ($searchValue) {
            $q->where(fn($x) => $x
                ->where('name', 'like', "%{$searchValue}%")
                ->orWhere('status', 'like', "%{$searchValue}%")
            );
        }

        $recordsFiltered = (clone $q)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = $request->input('order.0.dir', 'desc');
        $columns = [0 => 'id', 1 => 'name', 2 => 'status'];
        $q->orderBy($columns[$orderColIndex] ?? 'id', $orderDir);

        $rows = $q->skip($start)->take($length)->get();

        $data = $rows->map(function (WarrantyOption $w) {
            $statusBadge = $w->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $actions = '
              <div class="flex items-center gap-2">
                <button type="button" data-action="edit" data-id="'.$w->id.'" data-name="'.e($w->name).'"
                  class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">Edit</button>
                <button type="button" data-action="delete" data-id="'.$w->id.'" data-name="'.e($w->name).'"
                  class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
              </div>';

            return [
                'id' => $w->id,
                'name' => e($w->name),
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
