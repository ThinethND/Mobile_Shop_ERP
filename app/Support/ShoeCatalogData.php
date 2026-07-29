<?php

namespace App\Support;

class ShoeCatalogData
{
    public static function brands(): array
    {
        return [
            ['id' => 1, 'name' => 'Nike', 'status' => 'active', 'logo_url' => null],
            ['id' => 2, 'name' => 'Adidas', 'status' => 'active', 'logo_url' => null],
            ['id' => 3, 'name' => 'Puma', 'status' => 'active', 'logo_url' => null],
            ['id' => 4, 'name' => 'Reebok', 'status' => 'inactive', 'logo_url' => null],
        ];
    }

    public static function types(): array
    {
        return [
            ['id' => 1, 'name' => 'Sneakers', 'status' => 'active'],
            ['id' => 2, 'name' => 'Running', 'status' => 'active'],
            ['id' => 3, 'name' => 'Boots', 'status' => 'active'],
            ['id' => 4, 'name' => 'Sandals', 'status' => 'inactive'],
        ];
    }

    public static function categories(): array
    {
        return [
            ['id' => 1, 'name' => 'Men', 'status' => 'active'],
            ['id' => 2, 'name' => 'Women', 'status' => 'active'],
            ['id' => 3, 'name' => 'Kids', 'status' => 'active'],
        ];
    }

    public static function subcategories(): array
    {
        return [
            ['id' => 1, 'category_id' => 1, 'name' => 'Running', 'status' => 'active'],
            ['id' => 2, 'category_id' => 1, 'name' => 'Casual', 'status' => 'active'],
            ['id' => 3, 'category_id' => 2, 'name' => 'Training', 'status' => 'active'],
            ['id' => 4, 'category_id' => 2, 'name' => 'Lifestyle', 'status' => 'active'],
            ['id' => 5, 'category_id' => 3, 'name' => 'School', 'status' => 'active'],
            ['id' => 6, 'category_id' => 3, 'name' => 'Play', 'status' => 'inactive'],
        ];
    }

    public static function sizeTypes(): array
    {
        return [
            ['id' => 1, 'code' => 'UK', 'name' => 'UK Size', 'status' => 'active'],
            ['id' => 2, 'code' => 'US', 'name' => 'US Size', 'status' => 'active'],
            ['id' => 3, 'code' => 'EU', 'name' => 'EU Size', 'status' => 'active'],
            ['id' => 4, 'code' => 'CM', 'name' => 'Centimeter', 'status' => 'inactive'],
        ];
    }

    public static function colors(): array
    {
        return [
            ['id' => 1, 'name' => 'Black', 'hex_code' => '#000000', 'status' => 'active'],
            ['id' => 2, 'name' => 'White', 'hex_code' => '#FFFFFF', 'status' => 'active'],
            ['id' => 3, 'name' => 'Red', 'hex_code' => '#DC2626', 'status' => 'active'],
            ['id' => 4, 'name' => 'Blue', 'hex_code' => '#2563EB', 'status' => 'active'],
            ['id' => 5, 'name' => 'Orange', 'hex_code' => '#F97316', 'status' => 'inactive'],
        ];
    }

    public static function materials(): array
    {
        return [
            ['id' => 1, 'name' => 'Leather', 'status' => 'active'],
            ['id' => 2, 'name' => 'Mesh', 'status' => 'active'],
            ['id' => 3, 'name' => 'Canvas', 'status' => 'active'],
            ['id' => 4, 'name' => 'Suede', 'status' => 'inactive'],
        ];
    }

    public static function products(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Nike Air Zoom Alpha',
                'slug' => 'nike-air-zoom-alpha',
                'brand_id' => 1,
                'product_type_id' => 2,
                'category_id' => 1,
                'subcategory_id' => 1,
                'sku' => 'NZ-ALPHA-001',
                'barcode' => '1234567890123',
                'model_code' => 'AAZ-001',
                'short_description' => 'Responsive running shoe with lightweight upper.',
                'full_description' => 'Built for daily road runs with soft cushioning, breathable mesh, and a stable midsole platform.',
                'status' => 'published',
                'featured' => true,
                'new_arrival' => false,
                'best_seller' => true,
                'regular_price' => 34990,
                'sale_price' => 31990,
                'cost_price' => 25000,
                'currency' => 'LKR',
                'tax_class' => 'standard',
                'discount_type' => 'percentage',
                'discount_value' => '10',
                'sale_start_date' => '2026-03-01',
                'sale_end_date' => '2026-03-31',
                'stock_quantity' => 24,
                'low_stock_alert_quantity' => 5,
                'stock_status' => 'in_stock',
                'gender' => 'men',
                'age_group' => 'adult',
                'size_type_ids' => [1, 2],
                'sizes_by_type' => [
                    ['type' => 'UK', 'sizes' => ['8', '9', '9.5', '10']],
                    ['type' => 'US', 'sizes' => ['9', '10', '10.5', '11']],
                ],
                'color_ids' => [1, 2],
                'material_ids' => [2, 3],
                'shoe_weight' => '420g',
                'product_video_url' => 'https://example.com/video/nike-air-zoom-alpha',
                'thumbnail_url' => null,
                'gallery_urls' => [],
                'hover_image_url' => null,
            ],
        ];
    }

    public static function brand(int $id): array
    {
        return self::findOrFallback(self::brands(), $id, [
            'id' => $id,
            'name' => "Shoe Brand {$id}",
            'status' => 'active',
            'logo_url' => null,
        ]);
    }

    public static function type(int $id): array
    {
        return self::findOrFallback(self::types(), $id, [
            'id' => $id,
            'name' => "Shoe Type {$id}",
            'status' => 'active',
        ]);
    }

    public static function category(int $id): array
    {
        return self::findOrFallback(self::categories(), $id, [
            'id' => $id,
            'name' => "Shoe Category {$id}",
            'status' => 'active',
        ]);
    }

    public static function subcategory(int $id): array
    {
        return self::findOrFallback(self::subcategories(), $id, [
            'id' => $id,
            'category_id' => 1,
            'name' => "Shoe Subcategory {$id}",
            'status' => 'active',
        ]);
    }

    public static function sizeType(int $id): array
    {
        return self::findOrFallback(self::sizeTypes(), $id, [
            'id' => $id,
            'code' => "SIZE{$id}",
            'name' => "Size Type {$id}",
            'status' => 'active',
        ]);
    }

    public static function color(int $id): array
    {
        return self::findOrFallback(self::colors(), $id, [
            'id' => $id,
            'name' => "Color {$id}",
            'hex_code' => '',
            'status' => 'active',
        ]);
    }

    public static function material(int $id): array
    {
        return self::findOrFallback(self::materials(), $id, [
            'id' => $id,
            'name' => "Material {$id}",
            'status' => 'active',
        ]);
    }

    public static function product(int $id): array
    {
        $product = self::find(self::products(), $id);

        if ($product) {
            return $product;
        }

        $fallback = self::products()[0];
        $fallback['id'] = $id;
        $fallback['name'] = "Sample Shoe Product {$id}";
        $fallback['slug'] = "sample-shoe-product-{$id}";
        $fallback['sku'] = "SKU-{$id}";

        return $fallback;
    }

    public static function activeBrandOptions(): array
    {
        return self::mapOptions(self::brands());
    }

    public static function activeTypeOptions(): array
    {
        return self::mapOptions(self::types());
    }

    public static function activeCategoryOptions(): array
    {
        return self::mapOptions(self::categories());
    }

    public static function activeSubcategoryOptions(?int $categoryId = null): array
    {
        $items = array_filter(self::subcategories(), function (array $item) use ($categoryId) {
            if ($item['status'] !== 'active') {
                return false;
            }

            if ($categoryId === null) {
                return true;
            }

            return (int) $item['category_id'] === $categoryId;
        });

        return array_values(array_map(fn (array $item) => [
            'id' => $item['id'],
            'name' => $item['name'],
        ], $items));
    }

    public static function activeSizeTypeOptions(): array
    {
        $items = array_filter(self::sizeTypes(), fn (array $item) => $item['status'] === 'active');

        return array_values(array_map(fn (array $item) => [
            'id' => $item['id'],
            'name' => $item['code'],
        ], $items));
    }

    public static function activeColorOptions(): array
    {
        return self::mapOptions(self::colors());
    }

    public static function activeMaterialOptions(): array
    {
        return self::mapOptions(self::materials());
    }

    protected static function mapOptions(array $items): array
    {
        $items = array_filter($items, fn (array $item) => $item['status'] === 'active');

        return array_values(array_map(fn (array $item) => [
            'id' => $item['id'],
            'name' => $item['name'],
        ], $items));
    }

    protected static function find(array $items, int $id): ?array
    {
        foreach ($items as $item) {
            if ((int) $item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }

    protected static function findOrFallback(array $items, int $id, array $fallback): array
    {
        return self::find($items, $id) ?? $fallback;
    }
}