<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CosmeticProduct;
use App\Models\FashionProduct;
use App\Models\HomeNeedProduct;
use App\Models\MotorcycleProduct;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSuggestionController extends Controller
{
    public function suggestions(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $normalizedQuery = $this->normalize($query);
        $likeQuery = '%' . $normalizedQuery . '%';

        $electronicsProducts = Product::query()
            ->with([
                'brand:id,name',
                'category:id,name',
            ])
            ->where('status', 'active')
            ->where(function ($queryBuilder) use ($likeQuery) {
                $queryBuilder
                    ->whereRaw('LOWER(model) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(slug) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(sku) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(os) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(short_description) like ?', [$likeQuery])
                    ->orWhereHas('brand', function ($brandQuery) use ($likeQuery) {
                        $brandQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($likeQuery) {
                        $categoryQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    });
            })
            ->limit(8)
            ->get()
            ->map(function (Product $product) use ($normalizedQuery) {
                return [
                    'id' => 'electronics-' . $product->id,
                    'name' => (string) ($product->model ?: 'Product'),
                    'image_url' => $product->main_image_url ?: $product->hover_image_url,
                    'type' => 'electronics',
                    'type_label' => 'Electronics',
                    'target_url' => route('frontend.tech-products.show', [
                        'product' => $product->routeIdentifier(),
                    ]),
                    'rank' => $this->rankSuggestion((string) ($product->model ?: ''), $normalizedQuery),
                ];
            });

        $motorcycleProducts = MotorcycleProduct::query()
            ->with([
                'category:id,name',
                'helmetBrand:id,name',
            ])
            ->where('status', 'active')
            ->where(function ($queryBuilder) use ($likeQuery) {
                $queryBuilder
                    ->whereRaw('LOWER(name) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(slug) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(sku) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(product_type) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(short_description) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(full_description) like ?', [$likeQuery])
                    ->orWhereHas('helmetBrand', function ($brandQuery) use ($likeQuery) {
                        $brandQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($likeQuery) {
                        $categoryQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    });
            })
            ->limit(8)
            ->get()
            ->map(function (MotorcycleProduct $product) use ($normalizedQuery) {
                return [
                    'id' => 'motorcycle-' . $product->id,
                    'name' => (string) $product->name,
                    'image_url' => $product->main_image_url,
                    'type' => 'motorcycle',
                    'type_label' => 'Motorcycle',
                    'target_url' => route('frontend.motorcycle-products.show', [
                        'product' => filled($product->slug) ? $product->slug : $product->id,
                    ]),
                    'rank' => $this->rankSuggestion((string) $product->name, $normalizedQuery),
                ];
            });

        $cosmeticProducts = CosmeticProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
                'productType:id,name',
            ])
            ->where('status', 'active')
            ->where(function ($queryBuilder) use ($likeQuery) {
                $queryBuilder
                    ->whereRaw('LOWER(name) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(slug) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(batch_number) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(short_description) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(long_description) like ?', [$likeQuery])
                    ->orWhereHas('brand', function ($brandQuery) use ($likeQuery) {
                        $brandQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($likeQuery) {
                        $categoryQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    })
                    ->orWhereHas('productType', function ($typeQuery) use ($likeQuery) {
                        $typeQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    });
            })
            ->limit(8)
            ->get()
            ->map(function (CosmeticProduct $product) use ($normalizedQuery) {
                $identifier = filled($product->slug) ? $product->slug : $product->id;

                return [
                    'id' => 'cosmetic-' . $product->id,
                    'name' => (string) $product->name,
                    'image_url' => $product->main_image_url,
                    'type' => 'cosmetics',
                    'type_label' => 'Cosmetics',
                    'target_url' => route('frontend.cosmetic-products.show', [
                        'product' => $identifier,
                    ]),
                    'rank' => $this->rankSuggestion((string) $product->name, $normalizedQuery),
                ];
            });

        $fashionProducts = FashionProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
                'productType:id,name',
            ])
            ->where('status', 'active')
            ->where(function ($queryBuilder) use ($likeQuery) {
                $queryBuilder
                    ->whereRaw('LOWER(name) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(sku) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(brand_name) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(short_description) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(full_description) like ?', [$likeQuery])
                    ->orWhereHas('brand', function ($brandQuery) use ($likeQuery) {
                        $brandQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($likeQuery) {
                        $categoryQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    })
                    ->orWhereHas('productType', function ($typeQuery) use ($likeQuery) {
                        $typeQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    });
            })
            ->limit(8)
            ->get()
            ->map(function (FashionProduct $product) use ($normalizedQuery) {
                return [
                    'id' => 'fashion-' . $product->id,
                    'name' => (string) $product->name,
                    'image_url' => $product->main_image_url,
                    'type' => 'fashion',
                    'type_label' => 'Fashion',
                    'target_url' => route('frontend.fashion.show', [
                        'product' => filled($product->slug) ? $product->slug : $product->id,
                    ]),
                    'rank' => $this->rankSuggestion((string) $product->name, $normalizedQuery),
                ];
            });

        $homeNeedProducts = HomeNeedProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
            ])
            ->where('status', 'active')
            ->where(function ($queryBuilder) use ($likeQuery) {
                $queryBuilder
                    ->whereRaw('LOWER(name) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(sku) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(brand_name) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(short_description) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(full_description) like ?', [$likeQuery])
                    ->orWhereHas('brand', function ($brandQuery) use ($likeQuery) {
                        $brandQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($likeQuery) {
                        $categoryQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    });
            })
            ->limit(8)
            ->get()
            ->map(function (HomeNeedProduct $product) use ($normalizedQuery) {
                return [
                    'id' => 'home-need-' . $product->id,
                    'name' => (string) $product->name,
                    'image_url' => $product->main_image_url,
                    'type' => 'home-needs',
                    'type_label' => 'Home Needs',
                    'target_url' => route('frontend.home-needs.show', [
                        'product' => filled($product->slug) ? $product->slug : $product->id,
                    ]),
                    'rank' => $this->rankSuggestion((string) $product->name, $normalizedQuery),
                ];
            });

        $results = $electronicsProducts
            ->concat($motorcycleProducts)
            ->concat($cosmeticProducts)
            ->concat($fashionProducts)
            ->concat($homeNeedProducts)
            ->sort(function (array $a, array $b) {
                return [$a['rank'], $a['name']] <=> [$b['rank'], $b['name']];
            })
            ->take(10)
            ->values()
            ->map(function (array $item) {
                unset($item['rank']);
                return $item;
            });

        return response()->json($results);
    }

    private function rankSuggestion(string $name, string $query): int
    {
        $normalizedName = $this->normalize($name);

        if ($query === '') {
            return 999;
        }

        if ($normalizedName === $query) {
            return 0;
        }

        if (str_starts_with($normalizedName, $query)) {
            return 1;
        }

        foreach (preg_split('/\s+/', $normalizedName) ?: [] as $word) {
            if ($word !== '' && str_starts_with($word, $query)) {
                return 2;
            }
        }

        
        if (str_contains($normalizedName, $query)) {
            return 3;
        }

        return 4;
    }

    private function normalize(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }
}
