<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Boxibox database seeding...');

        // 1. Create the main data structure (sites, buildings, boxes, customers, contracts)
        $this->call([
            BoxiboxSeeder::class,
        ]);

        // 2. Add passwords to customers for API access
        $this->call([
            CustomerPasswordSeeder::class,
        ]);

        // 3. Add promotions
        $this->call([
            PromotionSeeder::class,
        ]);

        // 4. Add loyalty points and price rules (demo data)
        $this->call([
            LoyaltySeeder::class,
            PriceRuleSeeder::class,
        ]);

        $this->command->info('✨ Database seeding completed successfully!');
    }
}
