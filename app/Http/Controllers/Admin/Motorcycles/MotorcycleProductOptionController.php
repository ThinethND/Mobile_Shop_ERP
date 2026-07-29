<?php

namespace App\Http\Controllers\Admin\Motorcycles;

use App\Http\Controllers\Controller;
use App\Models\MotorcycleProductOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MotorcycleProductOptionController extends Controller
{
    public const LABELS = [
        'helmet_type' => 'Helmet Type',
        'helmet_size' => 'Helmet Size',
        'color' => 'Color',
        'accessory_type' => 'Accessory Type',
        'part_type' => 'Part Type',
    ];

    public function index(Request $request)
    {
        return Inertia::render('Motorcycles/ProductOptions/index', [
            'activeType' => $this->normalizeType($request->query('type', 'helmet_type')),
            'optionTypes' => $this->optionTypes(),
        ]);
    }

    public function create(Request $request)
    {
        $type = $this->normalizeType($request->query('type', 'helmet_type'));

        return Inertia::render('Motorcycles/ProductOptions/partials/CreateUpdate', [
            'mode' => 'create',
            'option' => [
                'type' => $type,
                'status' => 'active',
            ],
            'optionTypes' => $this->optionTypes(),
        ]);
    }

    public function edit(MotorcycleProductOption $option)
    {
        return Inertia::render('Motorcycles/ProductOptions/partials/CreateUpdate', [
            'mode' => 'edit',
            'option' => [
                'id' => $option->id,
                'type' => $option->type,
                'name' => $option->name,
                'status' => $option->status,
            ],
            'optionTypes' => $this->optionTypes(),
        ]);
    }

    public function data(Request $request)
    {
        $type = $this->normalizeType($request->query('type', 'helmet_type'));
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = MotorcycleProductOption::query()->where('type', $type);
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

        $data = $rows->map(function (MotorcycleProductOption $option) {
            $payload = e(json_encode([
                'id' => $option->id,
                'name' => $option->name,
            ], JSON_UNESCAPED_UNICODE));

            $statusBadge = $option->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $actions = '
                <div class="flex items-center gap-2">
                    <button type="button" data-action="edit" data-payload=\'' . $payload . '\' class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">Edit</button>
                    <button type="button" data-action="delete" data-payload=\'' . $payload . '\' class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
                </div>
            ';

            return [
                'id' => $option->id,
                'name' => e($option->name),
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
        $validated = $this->validateOption($request);
        MotorcycleProductOption::create($validated);

        return redirect()
            ->route('admin.motorcycles.options.index', ['type' => $validated['type']])
            ->with('success', 'Product option created.');
    }

    public function update(Request $request, MotorcycleProductOption $option)
    {
        $validated = $this->validateOption($request, $option);
        $option->update($validated);

        return redirect()
            ->route('admin.motorcycles.options.index', ['type' => $validated['type']])
            ->with('success', 'Product option updated.');
    }

    public function destroy(MotorcycleProductOption $option)
    {
        $type = $option->type;
        $option->delete();

        return redirect()
            ->route('admin.motorcycles.options.index', ['type' => $type])
            ->with('success', 'Product option deleted.');
    }

    public function options(Request $request)
    {
        $type = $request->query('type');

        $options = MotorcycleProductOption::query()
            ->where('status', 'active')
            ->when($type, fn ($query) => $query->where('type', $this->normalizeType($type)))
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (MotorcycleProductOption $option) => [
                'id' => $option->id,
                'type' => $option->type,
                'name' => $option->name,
                'value' => $option->id,
                'label' => $option->name,
            ])
            ->values();

        return response()->json($options);
    }

    protected function validateOption(Request $request, ?MotorcycleProductOption $option = null): array
    {
        return $request->validate([
            'type' => ['required', 'string', Rule::in(MotorcycleProductOption::TYPES)],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('motorcycle_product_options', 'name')
                    ->ignore($option?->id)
                    ->where(fn ($query) => $query->where('type', $request->input('type'))),
            ],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    protected function normalizeType(?string $type): string
    {
        return in_array($type, MotorcycleProductOption::TYPES, true) ? $type : 'helmet_type';
    }

    protected function optionTypes(): array
    {
        return collect(MotorcycleProductOption::TYPES)
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => self::LABELS[$type] ?? $type,
            ])
            ->values()
            ->all();
    }
}
