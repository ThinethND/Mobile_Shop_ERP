<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoyaltyTier;

class LoyaltyTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name'          => 'Bronze',
                'description'   => 'Entry tier for new customers. Earn points on every booking.',
                'start_points'  => 0,
                'end_points'    => 499,

            ],
            [
                'name'          => 'Silver',
                'description'   => 'Mid-level tier with exclusive benefits.',
                'start_points'  => 500,
                'end_points'    => 1499,


            ],
            [
                'name'          => 'Gold',
                'description'   => 'Premium tier for our top customers.',
                'start_points'  => 1500,
                'end_points'    => 999999,

            ],
        ];

        foreach ($tiers as $tier) {
            LoyaltyTier::updateOrCreate(
                [
                    'name'          => $tier['name'],
                    'description'   => $tier['description'],
                    'start_points'  => $tier['start_points'],
                    'end_points'    => $tier['end_points'],

                ]
            );
        }
    }
}
