<?php

namespace App\Http\Middleware;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CosmeticBrand;
use App\Models\CosmeticCategory;
use App\Models\CosmeticProduct;
use App\Models\ShoeCategory;
use App\Support\PageSeo;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Spatie\Permission\Models\Permission;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = Auth::user();
        $shared = [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),

                ] : null,
            ],

            'permission' => $user ? $this->getPermissions($user) : [],

        ];

        if (!$request->is('admin*')) {
            $shared['seo'] = function () use ($request) {
                $seoContext = $request->attributes->get('seo_context');

                if (is_array($seoContext)) {
                    return $seoContext;
                }

                $seoContext = app(PageSeo::class)->forRequest($request);
                $request->attributes->set('seo_context', $seoContext);

                return $seoContext;
            };

            $shared['categories'] = fn () => Category::query()
                ->select('id', 'name', 'image_path', 'status')
                ->where('status', 'active')
                ->with([
                    'brands' => function ($query) {
                        $query->where('brands.status', 'active')
                            ->orderBy('brands.name')
                            ->select('brands.id', 'brands.name', 'brands.logo_path', 'brands.status');
                    }
                ])
                ->oldest('id')
                ->get()
                ->map(function (Category $category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image_url' => $category->image_url,
                        'status' => $category->status,
                        'brands' => $category->brands->map(function (Brand $brand) {
                            return [
                                'id' => $brand->id,
                                'name' => $brand->name,
                                'logo_url' => $brand->logo_url,
                                'status' => $brand->status,
                            ];
                        })->values(),
                    ];
                })
                ->values();

            $shared['shoeCategories'] = fn () => ShoeCategory::query()
                ->select('id', 'name', 'image_path', 'status')
                ->where('status', 'active')
                ->with([
                    'subcategories' => function ($query) {
                        $query->where('status', 'active')
                            ->oldest('id')
                            ->select('id', 'category_id', 'name', 'image_path', 'status');
                    }
                ])
                ->oldest('id')
                ->get()
                ->map(function (ShoeCategory $category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image_url' => $category->image_url,
                        'status' => $category->status,
                        'subcategories' => $category->subcategories->map(function ($subcategory) {
                            return [
                                'id' => $subcategory->id,
                                'name' => $subcategory->name,
                                'image_url' => $subcategory->image_url,
                                'status' => $subcategory->status,
                            ];
                        })->values(),
                    ];
                })
                ->values();

            $shared['cosmeticBrands'] = fn () => $this->cosmeticNavBrands();
        }

        return $shared;
    }

    private function cosmeticNavBrands()
    {
        $pairs = CosmeticProduct::query()
            ->select(['brand_id', 'category_id'])
            ->where('status', 'active')
            ->whereNotNull('brand_id')
            ->whereNotNull('category_id')
            ->distinct()
            ->get();

        $brandIds = $pairs->pluck('brand_id')->filter()->unique()->values();
        $categoryIds = $pairs->pluck('category_id')->filter()->unique()->values();

        $brands = CosmeticBrand::query()
            ->whereIn('id', $brandIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'logo_path', 'status'])
            ->keyBy('id');

        $categories = CosmeticCategory::query()
            ->whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->keyBy('id');

        $brandCategoryMap = [];

        foreach ($pairs as $pair) {
            $brandId = (int) $pair->brand_id;
            $categoryId = (int) $pair->category_id;

            if (!isset($brandCategoryMap[$brandId])) {
                $brandCategoryMap[$brandId] = [];
            }

            $brandCategoryMap[$brandId][$categoryId] = true;
        }

        return $brands
            ->values()
            ->map(function (CosmeticBrand $brand) use ($brandCategoryMap, $categories) {
                $categoryIdSet = $brandCategoryMap[(int) $brand->id] ?? [];

                $brandCategories = collect(array_keys($categoryIdSet))
                    ->map(fn ($categoryId) => $categories->get((int) $categoryId))
                    ->filter()
                    ->sortBy('name')
                    ->map(fn (CosmeticCategory $category) => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ])
                    ->values();

                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'logo_url' => $brand->logo_url,
                    'status' => $brand->status,
                    'categories' => $brandCategories,
                ];
            })
            ->filter(fn (array $brand) => count($brand['categories'] ?? []) > 0)
            ->values();
    }

    private function getPermissions($user): array
    {
        if ($user->hasRole('Super Admin')) {
            return Permission::pluck('name')->mapWithKeys(fn($p) => [$p => true])->toArray();
        }

        return $user->getAllPermissions()
            ->pluck('name')
            ->mapWithKeys(fn($p) => [$p => true])
            ->toArray();
    }
}
