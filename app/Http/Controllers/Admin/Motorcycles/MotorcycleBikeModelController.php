<?php

namespace App\Http\Controllers\Admin\Motorcycles;

use App\Http\Controllers\Controller;
use App\Models\MotorcycleBikeBrand;
use App\Models\MotorcycleBikeModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MotorcycleBikeModelController extends Controller
{
    public function index()
    {
        return Inertia::render('Motorcycles/BikeModels/index');
    }

    public function create()
    {
        return Inertia::render('Motorcycles/BikeModels/partials/CreateUpdate', [
            'mode' => 'create',
            'bikeModel' => null,
            'bikeBrands' => $this->brandOptions(),
        ]);
    }

    public function edit(MotorcycleBikeModel $bikeModel)
    {
        $bikeModel->load('brand:id,name');

        return Inertia::render('Motorcycles/BikeModels/partials/CreateUpdate', [
            'mode' => 'edit',
            'bikeModel' => [
                'id' => $bikeModel->id,
                'bike_brand_id' => $bikeModel->bike_brand_id,
                'bike_brand_name' => $bikeModel->brand?->name,
                'name' => $bikeModel->name,
                'start_year' => $bikeModel->start_year,
                'end_year' => $bikeModel->end_year,
                'engine_cc' => $bikeModel->engine_cc,
                'status' => $bikeModel->status,
            ],
            'bikeBrands' => $this->brandOptions(),
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));

        $baseQuery = MotorcycleBikeModel::query()->with('brand:id,name,status');
        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhere('engine_cc', 'like', "%{$searchValue}%")
                    ->orWhereHas('brand', fn ($x) => $x->where('name', 'like', "%{$searchValue}%"));
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();
        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $columns = [
            0 => 'id',
            2 => 'name',
            3 => 'start_year',
            4 => 'engine_cc',
            5 => 'status',
        ];
        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (MotorcycleBikeModel $bikeModel) {
            $payload = e(json_encode([
                'id' => $bikeModel->id,
                'name' => $bikeModel->brand?->name . ' ' . $bikeModel->name,
            ], JSON_UNESCAPED_UNICODE));

            $yearRange = $bikeModel->start_year || $bikeModel->end_year
                ? e(($bikeModel->start_year ?: '-') . ' - ' . ($bikeModel->end_year ?: 'Present'))
                : '<span class="text-neutral-400">-</span>';

            $statusBadge = $bikeModel->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $actions = '
                <div class="flex items-center gap-2">
                    <button type="button" data-action="edit" data-payload=\'' . $payload . '\' class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">Edit</button>
                    <button type="button" data-action="delete" data-payload=\'' . $payload . '\' class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
                </div>
            ';

            return [
                'id' => $bikeModel->id,
                'brand' => e($bikeModel->brand?->name ?? '-'),
                'model' => e($bikeModel->name),
                'year_range' => $yearRange,
                'engine_cc' => $bikeModel->engine_cc ? e((string) $bikeModel->engine_cc) : '<span class="text-neutral-400">-</span>',
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
        $validated = $this->validateBikeModel($request);
        $brand = $this->resolveBrand($validated['bike_brand_id'] ?? null, $validated['bike_brand_name']);

        MotorcycleBikeModel::create([
            'bike_brand_id' => $brand->id,
            'name' => $validated['name'],
            'start_year' => $validated['start_year'] ?? null,
            'end_year' => $validated['end_year'] ?? null,
            'engine_cc' => $validated['engine_cc'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.motorcycles.bike-models.index')
            ->with('success', 'Bike brand and model created.');
    }

    public function update(Request $request, MotorcycleBikeModel $bikeModel)
    {
        $validated = $this->validateBikeModel($request, $bikeModel);
        $brand = $this->resolveBrand($validated['bike_brand_id'] ?? null, $validated['bike_brand_name']);

        $bikeModel->update([
            'bike_brand_id' => $brand->id,
            'name' => $validated['name'],
            'start_year' => $validated['start_year'] ?? null,
            'end_year' => $validated['end_year'] ?? null,
            'engine_cc' => $validated['engine_cc'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.motorcycles.bike-models.index')
            ->with('success', 'Bike brand and model updated.');
    }

    public function destroy(MotorcycleBikeModel $bikeModel)
    {
        $bikeModel->delete();

        return redirect()
            ->route('admin.motorcycles.bike-models.index')
            ->with('success', 'Bike model deleted.');
    }

    public function options()
    {
        $brands = MotorcycleBikeBrand::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->with(['models' => fn ($query) => $query->where('status', 'active')->orderBy('name')])
            ->get();

        return response()->json([
            'brands' => $brands->map(fn (MotorcycleBikeBrand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'value' => $brand->id,
                'label' => $brand->name,
            ])->values(),
            'models' => $brands->flatMap(fn (MotorcycleBikeBrand $brand) => $brand->models->map(fn (MotorcycleBikeModel $model) => [
                'id' => $model->id,
                'bike_brand_id' => $brand->id,
                'name' => $model->name,
                'label' => $model->name,
                'value' => $model->id,
                'start_year' => $model->start_year,
                'end_year' => $model->end_year,
                'engine_cc' => $model->engine_cc,
            ]))->values(),
        ]);
    }

    protected function validateBikeModel(Request $request, ?MotorcycleBikeModel $bikeModel = null): array
    {
        return $request->validate([
            'bike_brand_id' => ['nullable', 'integer', 'exists:motorcycle_bike_brands,id'],
            'bike_brand_name' => ['required_without:bike_brand_id', 'nullable', 'string', 'max:255'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('motorcycle_bike_models', 'name')
                    ->ignore($bikeModel?->id)
                    ->where(fn ($query) => $query->where('bike_brand_id', $request->input('bike_brand_id'))),
            ],
            'start_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'end_year' => ['nullable', 'integer', 'min:1950', 'max:2100', 'gte:start_year'],
            'engine_cc' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    protected function resolveBrand(?int $brandId, ?string $brandName): MotorcycleBikeBrand
    {
        if ($brandId) {
            return MotorcycleBikeBrand::query()->findOrFail($brandId);
        }

        return MotorcycleBikeBrand::query()->firstOrCreate(
            ['name' => trim((string) $brandName)],
            ['status' => 'active']
        );
    }

    protected function brandOptions()
    {
        return MotorcycleBikeBrand::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (MotorcycleBikeBrand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'value' => $brand->id,
                'label' => $brand->name,
            ])
            ->values();
    }
}
