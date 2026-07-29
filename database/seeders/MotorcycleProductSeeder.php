<?php

namespace Database\Seeders;

use App\Models\MotorcycleBikeBrand;
use App\Models\MotorcycleBikeModel;
use App\Models\MotorcycleHelmetBrand;
use App\Models\MotorcycleProductCategory;
use App\Models\MotorcycleProductOption;
use Illuminate\Database\Seeder;

class MotorcycleProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['KYT', 'LS2', 'AGV', 'HJC', 'MT Helmets', 'Studds', 'Steelbird'] as $brand) {
            MotorcycleHelmetBrand::query()->firstOrCreate(
                ['name' => $brand],
                ['status' => 'active']
            );
        }

        $categoryMap = [
            'Helmets' => [],
            'Helmet Accessories' => ['Visors', 'Helmet Bags', 'Bluetooth Intercoms', 'Inner Padding'],
            'Bike Accessories' => [],
            'Spare Parts' => [],
            'Riding Gear' => [],
        ];

        foreach ($categoryMap as $categoryName => $children) {
            $parent = MotorcycleProductCategory::query()->firstOrCreate(
                ['name' => $categoryName, 'parent_category_id' => null],
                ['status' => 'active']
            );

            foreach ($children as $childName) {
                MotorcycleProductCategory::query()->firstOrCreate(
                    ['name' => $childName, 'parent_category_id' => $parent->id],
                    ['status' => 'active']
                );
            }
        }

        $options = [
            'helmet_type' => ['Full Face', 'Open Face', 'Modular', 'Half Face', 'Off Road'],
            'helmet_size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            'color' => ['Black', 'White', 'Red', 'Blue', 'Grey'],
            'accessory_type' => ['Mirror', 'Phone Holder', 'Tank Pad', 'Visor', 'Helmet Bag', 'LED Light'],
            'part_type' => ['Brake Pad', 'Air Filter', 'Oil Filter', 'Chain Sprocket', 'Spark Plug', 'Battery', 'Cable', 'Lever'],
        ];

        foreach ($options as $type => $names) {
            foreach ($names as $name) {
                MotorcycleProductOption::query()->firstOrCreate(
                    ['type' => $type, 'name' => $name],
                    ['status' => 'active']
                );
            }
        }

        $bikeMap = [
            'Yamaha' => [
                ['name' => 'FZ', 'start_year' => 2018, 'end_year' => 2024, 'engine_cc' => 150],
                ['name' => 'R15', 'start_year' => 2017, 'end_year' => 2024, 'engine_cc' => 155],
                ['name' => 'MT-15', 'start_year' => 2019, 'end_year' => 2024, 'engine_cc' => 155],
            ],
            'Honda' => [
                ['name' => 'Dio', 'start_year' => 2015, 'end_year' => 2024, 'engine_cc' => 110],
                ['name' => 'CB Hornet', 'start_year' => 2016, 'end_year' => 2024, 'engine_cc' => 160],
                ['name' => 'CBR', 'start_year' => 2011, 'end_year' => 2024, 'engine_cc' => 250],
            ],
        ];

        foreach ($bikeMap as $brandName => $models) {
            $brand = MotorcycleBikeBrand::query()->firstOrCreate(
                ['name' => $brandName],
                ['status' => 'active']
            );

            foreach ($models as $model) {
                MotorcycleBikeModel::query()->firstOrCreate(
                    [
                        'bike_brand_id' => $brand->id,
                        'name' => $model['name'],
                    ],
                    [
                        'start_year' => $model['start_year'],
                        'end_year' => $model['end_year'],
                        'engine_cc' => $model['engine_cc'],
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
