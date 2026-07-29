<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Models\ShoeSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
class ShoeSubcategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Subcategories/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Subcategories/partials/CreateUpdate', [
            'mode' => 'create',
            'subcategory' => null,
        ]);
    }

    public function edit(ShoeSubcategory $subcategory)
    {
        return Inertia::render('Shoes/Subcategories/partials/CreateUpdate', [
            'mode' => 'edit',
'subcategory' => [
    'id' => $subcategory->id,
    'category_id' => $subcategory->category_id,
    'name' => $subcategory->name,
    'status' => $subcategory->status,
    'image_url' => $subcategory->image_url,
],
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = ShoeSubcategory::query()->with('category:id,name');

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($searchValue) {
                        $categoryQuery->where('name', 'like', "%{$searchValue}%");
                    });
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'category_id',
            2 => 'name',
            3 => 'status',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (ShoeSubcategory $subcategory) {
            $statusBadge = $subcategory->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $payload = e(json_encode([
                'id' => $subcategory->id,
                'category_id' => $subcategory->category_id,
                'name' => $subcategory->name,
                'status' => $subcategory->status,
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
                'id' => $subcategory->id,
                'category_name' => e($subcategory->category?->name ?? '-'),
                'name' => e($subcategory->name),
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
    'category_id' => ['required', 'integer', 'exists:shoes_categories,id'],
    'name' => [
        'required',
        'string',
        'max:255',
        Rule::unique('shoe_subcategories', 'name')
            ->where(fn ($query) => $query->where('category_id', $request->category_id)),
    ],
    'status' => ['required', 'in:active,inactive'],
    'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
]);

       $imagePath = $request->hasFile('image')
    ? $request->file('image')->store('shoe-subcategories', 'public')
    : null;

ShoeSubcategory::create([
    'category_id' => $validated['category_id'],
    'name' => $validated['name'],
    'image_path' => $imagePath,
    'status' => $validated['status'],
]);
        return redirect()
            ->route('admin.shoes.subcategories.index')
            ->with('success', 'Shoe subcategory created.');
    }

    public function update(Request $request, ShoeSubcategory $subcategory)
    {
       $validated = $request->validate([
    'category_id' => ['required', 'integer', 'exists:shoes_categories,id'],
    'name' => [
        'required',
        'string',
        'max:255',
        Rule::unique('shoe_subcategories', 'name')
            ->ignore($subcategory->id)
            ->where(fn ($query) => $query->where('category_id', $request->category_id)),
    ],
    'status' => ['required', 'in:active,inactive'],
    'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
]);

       $data = [
    'category_id' => $validated['category_id'],
    'name' => $validated['name'],
    'status' => $validated['status'],
];

if ($request->hasFile('image')) {
    if ($subcategory->image_path && Storage::disk('public')->exists($subcategory->image_path)) {
        Storage::disk('public')->delete($subcategory->image_path);
    }

    $data['image_path'] = $request->file('image')->store('shoe-subcategories', 'public');
}

$subcategory->update($data);

        return redirect()
            ->route('admin.shoes.subcategories.index')
            ->with('success', 'Shoe subcategory updated.');
    }
public function destroy(ShoeSubcategory $subcategory)
{
    if ($subcategory->image_path && Storage::disk('public')->exists($subcategory->image_path)) {
        Storage::disk('public')->delete($subcategory->image_path);
    }

    $subcategory->delete();

    return redirect()
        ->route('admin.shoes.subcategories.index')
        ->with('success', 'Shoe subcategory deleted.');
}

    public function options(Request $request)
{
    $categoryId = $request->integer('category_id');

    $subcategories = ShoeSubcategory::query()
        ->select('id', 'name', 'category_id')
        ->where('status', 'active')
        ->when($categoryId, function ($query) use ($categoryId) {
            $query->where('category_id', $categoryId);
        })
        ->orderBy('name')
        ->get()
        ->map(fn (ShoeSubcategory $subcategory) => [
            'id' => $subcategory->id,
            'name' => $subcategory->name,
        ])
        ->values();

    return response()->json($subcategories);
}
}