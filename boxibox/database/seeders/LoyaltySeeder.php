<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use Illuminate\Database\Seeder;

class LoyaltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all customers
        $customers = Customer::all();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run BoxiboxSeeder first.');
            return;
        }

        $this->command->info('Creating loyalty points for ' . $customers->count() . ' customers...');

        foreach ($customers as $customer) {
            // Create loyalty point record for each customer
            $loyalty = LoyaltyPoint::create([
                'customer_id' => $customer->id,
                'points' => 0,
                'points_earned' => 0,
                'points_spent' => 0,
                'tier' => 'bronze',
            ]);

            // Simulate different scenarios for customers

            // 30% of customers are new (bronze tier, few points)
            if (rand(1, 100) <= 30) {
                $points = rand(0, 500);
                $loyalty->addPoints($points, 'Points de bienvenue', null);
            }
            // 40% are regular customers (silver tier)
            elseif (rand(1, 100) <= 70) {
                // Add points for contract
                $loyalty->addPoints(100, 'Nouveau contrat', null);

                // Add points for monthly payments (simulate several months)
                $months = rand(3, 15);
                for ($i = 0; $i < $months; $i++) {
                    $loyalty->addPoints(10, 'Paiement mensuel', null);
                }

                // Maybe they spent some points
                if (rand(1, 100) <= 40) {
                    $pointsToSpend = rand(50, 200);
                    if ($loyalty->points >= $pointsToSpend) {
                        $loyalty->spendPoints($pointsToSpend, 'Réduction sur renouvellement', null);
                    }
                }
            }
            // 20% are loyal customers (gold tier)
            elseif (rand(1, 100) <= 90) {
                // Add points for contract
                $loyalty->addPoints(100, 'Nouveau contrat', null);

                // Add points for many monthly payments
                $months = rand(15, 40);
                for ($i = 0; $i < $months; $i++) {
                    $loyalty->addPoints(10, 'Paiement mensuel', null);
                }

                // Add referral bonus
                if (rand(1, 100) <= 60) {
                    $loyalty->addPoints(50, 'Bonus parrainage', null);
                }

                // Spent some points
                if (rand(1, 100) <= 60) {
                    $pointsToSpend = rand(200, 500);
                    if ($loyalty->points >= $pointsToSpend) {
                        $loyalty->spendPoints($pointsToSpend, 'Réduction sur renouvellement', null);
                    }
                }
            }
            // 10% are VIP customers (platinum tier)
            else {
                // Add points for contract
                $loyalty->addPoints(100, 'Nouveau contrat', null);

                // Add points for many monthly payments (long-term customers)
                $months = rand(40, 80);
                for ($i = 0; $i < $months; $i++) {
                    $loyalty->addPoints(10, 'Paiement mensuel', null);
                }

                // Multiple referrals
                $referrals = rand(2, 5);
                for ($i = 0; $i < $referrals; $i++) {
                    $loyalty->addPoints(50, 'Bonus parrainage', null);
                }

                // Add special bonus
                $loyalty->addPoints(200, 'Bonus client fidèle', null);

                // Spent points multiple times
                $redemptions = rand(2, 4);
                for ($i = 0; $i < $redemptions; $i++) {
                    $pointsToSpend = rand(500, 1000);
                    if ($loyalty->points >= $pointsToSpend) {
                        $loyalty->spendPoints($pointsToSpend, 'Réduction sur facture', null);
                    }
                }
            }
        }

        // Count customers by tier
        $bronzeCount = LoyaltyPoint::where('tier', 'bronze')->count();
        $silverCount = LoyaltyPoint::where('tier', 'silver')->count();
        $goldCount = LoyaltyPoint::where('tier', 'gold')->count();
        $platinumCount = LoyaltyPoint::where('tier', 'platinum')->count();

        $this->command->info('✅ Loyalty points created successfully!');
        $this->command->info('📊 Distribution by tier:');
        $this->command->info("   - Bronze: $bronzeCount clients");
        $this->command->info("   - Silver: $silverCount clients");
        $this->command->info("   - Gold: $goldCount clients");
        $this->command->info("   - Platinum: $platinumCount clients");
    }
}
