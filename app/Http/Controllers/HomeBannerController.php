<?php

namespace App\Http\Controllers;

use App\Models\HomeBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class HomeBannerController extends Controller
{
    public function index()
    {
        return Inertia::render('OtherCMS/HomeBanner/index');
    }

    public function create()
    {
        return Inertia::render('OtherCMS/HomeBanner/partials/CreateUpdate', [
            'mode' => 'create',
            'banner' => null,
        ]);
    }

    public function edit(HomeBanner $homeBanner)
    {
        return Inertia::render('OtherCMS/HomeBanner/partials/CreateUpdate', [
            'mode' => 'edit',
            'banner' => [
                'id' => $homeBanner->id,
                'name' => $homeBanner->name,
                'description' => $homeBanner->description,
                'desktop_image_url' => $homeBanner->desktop_image_url,
                'mobile_image_url' => $homeBanner->mobile_image_url,
                'video_url' => $homeBanner->video_url,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:home_banners,name'],
            'description' => ['nullable', 'string'],
            'desktop_image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
            'mobile_image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
        ]);

        $desktopPath = $request->file('desktop_image')->store('homebanners/desktop', 'public');
        $mobilePath = $request->file('mobile_image')->store('homebanners/mobile', 'public');

        HomeBanner::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'desktop_image_path' => $desktopPath,
            'mobile_image_path' => $mobilePath,
        ]);

        return redirect()->route('homebanners.index')->with('success', 'Home banner created.');
    }

    public function update(Request $request, HomeBanner $homeBanner)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:home_banners,name,' . $homeBanner->id],
            'description' => ['nullable', 'string'],
            'desktop_image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
            'mobile_image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
        ]);

        if ($request->hasFile('desktop_image')) {
            if ($homeBanner->desktop_image_path && Storage::disk('public')->exists($homeBanner->desktop_image_path)) {
                Storage::disk('public')->delete($homeBanner->desktop_image_path);
            }

            $homeBanner->desktop_image_path = $request->file('desktop_image')->store('homebanners/desktop', 'public');
        }

        if ($request->hasFile('mobile_image')) {
            if ($homeBanner->mobile_image_path && Storage::disk('public')->exists($homeBanner->mobile_image_path)) {
                Storage::disk('public')->delete($homeBanner->mobile_image_path);
            }

            $homeBanner->mobile_image_path = $request->file('mobile_image')->store('homebanners/mobile', 'public');
        }

        $homeBanner->name = $validated['name'];
        $homeBanner->description = $validated['description'] ?? null;
        $homeBanner->save();

        return redirect()->route('homebanners.index')->with('success', 'Home banner updated.');
    }

    public function destroy(HomeBanner $homeBanner)
    {
        if ($homeBanner->desktop_image_path && Storage::disk('public')->exists($homeBanner->desktop_image_path)) {
            Storage::disk('public')->delete($homeBanner->desktop_image_path);
        }

        if ($homeBanner->mobile_image_path && Storage::disk('public')->exists($homeBanner->mobile_image_path)) {
            Storage::disk('public')->delete($homeBanner->mobile_image_path);
        }

        if ($homeBanner->video_path && Storage::disk('public')->exists($homeBanner->video_path)) {
            Storage::disk('public')->delete($homeBanner->video_path);
        }

        $homeBanner->delete();

        return redirect()->route('homebanners.index')->with('success', 'Home banner deleted.');
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = $request->input('search.value');

        $q = HomeBanner::query();
        $recordsTotal = (clone $q)->count();

        if ($searchValue) {
            $q->where(function ($x) use ($searchValue) {
                $x->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('description', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $q)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'desktop_image_path',
            3 => 'mobile_image_path',
            4 => 'description',
        ];

        $q->orderBy($columns[$orderColIndex] ?? 'id', $orderDir);

        $rows = $q->skip($start)->take($length)->get();

        $data = $rows->map(function (HomeBanner $b) {
            $desc = e(Str::limit(strip_tags($b->description ?? ''), 90));

            $actions = '
              <div class="flex items-center gap-2">
                <button type="button" data-action="edit" data-id="'.$b->id.'" data-name="'.e($b->name).'"
                  class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">Edit</button>
                <button type="button" data-action="delete" data-id="'.$b->id.'" data-name="'.e($b->name).'"
                  class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
              </div>';

            return [
                'id' => $b->id,
                'name' => e($b->name),
                'desktop_image_url' => $b->desktop_image_url,
                'mobile_image_url' => $b->mobile_image_url,
                'description' => $desc,
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