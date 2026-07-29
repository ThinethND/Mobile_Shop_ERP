<?php

namespace App\Http\Controllers;

use App\Models\ColorOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ColorOptionController extends Controller
{
    public function index()
    {
        return Inertia::render('Colors/index');
    }

    public function create()
    {
        return Inertia::render('Colors/partials/CreateUpdate', [
            'mode' => 'create',
            'color' => null,
        ]);
    }

    public function edit(ColorOption $colorOption)
    {
        return Inertia::render('Colors/partials/CreateUpdate', [
            'mode' => 'edit',
            'color' => [
                'id' => $colorOption->id,
                'name' => $colorOption->name,
                'status' => $colorOption->status,
                'color_code' => $colorOption->color_code,
                'image_url' => $colorOption->image_url,
            ],
        ]);
    }

    public function image(ColorOption $colorOption)
    {
        $fill = $colorOption->normalizedColorCode();

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">
  <circle cx="40" cy="40" r="38" fill="{$fill}" stroke="#E5E7EB" stroke-width="2"/>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255', 'unique:color_options,name'],
            'status'     => ['required', 'in:active,inactive'],
            'color_code' => ['required', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
        ]);

        ColorOption::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
            'color_code' => strtoupper($validated['color_code']),
            'image_path' => null,
        ]);

        return redirect()->route('colors.index')->with('success', 'Color created.');
    }

    public function update(Request $request, ColorOption $colorOption)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255', 'unique:color_options,name,' . $colorOption->id],
            'status'     => ['required', 'in:active,inactive'],
            'color_code' => ['required', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
        ]);

        if ($colorOption->image_path && Storage::disk('public')->exists($colorOption->image_path)) {
            Storage::disk('public')->delete($colorOption->image_path);
        }

        $colorOption->name = $validated['name'];
        $colorOption->status = $validated['status'];
        $colorOption->color_code = strtoupper($validated['color_code']);
        $colorOption->image_path = null;
        $colorOption->save();

        return redirect()->route('colors.index')->with('success', 'Color updated.');
    }

    public function destroy(ColorOption $colorOption)
    {
        if ($colorOption->image_path && Storage::disk('public')->exists($colorOption->image_path)) {
            Storage::disk('public')->delete($colorOption->image_path);
        }

        $colorOption->delete();

        return redirect()->route('colors.index')->with('success', 'Color deleted.');
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = $request->input('search.value');

        $q = ColorOption::query();
        $recordsTotal = (clone $q)->count();

        if ($searchValue) {
            $q->where(function ($x) use ($searchValue) {
                $x->where('name', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%")
                  ->orWhere('color_code', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $q)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = $request->input('order.0.dir', 'desc');

        $columns = [
            0 => 'id',
            1 => 'color_code',
            2 => 'name',
            3 => 'status',
        ];

        $q->orderBy($columns[$orderColIndex] ?? 'id', $orderDir);

        $rows = $q->skip($start)->take($length)->get();

        $data = $rows->map(function (ColorOption $c) {
            $statusBadge = $c->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $swatchHtml = '<div class="flex items-center justify-center">
                    <div class="h-12 w-12 rounded-full border border-neutral-300 bg-white" style="background-color:' . e($c->normalizedColorCode()) . ';"></div>
                </div>';

            $actions = '
              <div class="flex items-center gap-2">
                <button type="button" data-action="edit" data-id="'.$c->id.'" data-name="'.e($c->name).'"
                  class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">Edit</button>
                <button type="button" data-action="delete" data-id="'.$c->id.'" data-name="'.e($c->name).'"
                  class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
              </div>';

            return [
                'id' => $c->id,
                'image_html' => $swatchHtml,
                'name' => e($c->name),
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