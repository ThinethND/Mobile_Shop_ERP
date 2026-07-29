<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\LoyaltyTier;

class CheckCustomerTiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loyalty:check-tiers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all customers and update their loyalty tier based on lifetime points';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tiers = LoyaltyTier::all();

        Client::chunk(200, function ($customers) use ($tiers) {
            foreach ($customers as $customer) {

                foreach ($tiers as $tier) {
                    if (
                        $customer->lifetime_points >= $tier->start_points &&
                        $customer->lifetime_points <= $tier->end_points
                    ) {
                        if ($customer->loyalty_tier_id != $tier->id) {
                            $customer->loyalty_tier_id = $tier->id;
                            $customer->save();
                        }
                        break;
                    }
                }
            }
        });

        $this->info('Customer loyalty tiers updated successfully.');
    }
}
