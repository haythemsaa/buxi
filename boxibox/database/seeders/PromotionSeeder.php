<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = [
            [
                'code' => 'BIENVENUE30',
                'name' => 'Bienvenue 30%',
                'description' => '30% de réduction sur le premier mois pour les nouveaux clients',
                'discount_type' => 'percentage',
                'discount_value' => 30.00,
                'free_months' => 0,
                'min_duration_months' => 3,
                'min_amount' => null,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(6),
                'is_active' => true,
                'max_uses' => 100,
                'current_uses' => 0,
                'max_uses_per_customer' => 1,
                'online_only' => true,
                'new_customers_only' => true,
                'is_public' => true,
                'requires_code' => true,
                'auto_apply' => false,
                'priority' => 10,
                'stackable' => false,
            ],
            [
                'code' => 'ETE2025',
                'name' => 'Promo Été 2025',
                'description' => '2 mois gratuits pour tout engagement de 12 mois',
                'discount_type' => 'months_free',
                'discount_value' => 0,
                'free_months' => 2,
                'min_duration_months' => 12,
                'min_amount' => null,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now()->addMonths(3),
                'valid_until' => now()->addMonths(6),
                'is_active' => true,
                'max_uses' => 50,
                'current_uses' => 0,
                'max_uses_per_customer' => 1,
                'online_only' => true,
                'new_customers_only' => false,
                'is_public' => true,
                'requires_code' => true,
                'auto_apply' => false,
                'priority' => 8,
                'stackable' => false,
            ],
            [
                'code' => 'ONLINE15',
                'name' => 'Réduction Online',
                'description' => '15% de réduction pour toute réservation en ligne',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'free_months' => 0,
                'min_duration_months' => 1,
                'min_amount' => null,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'is_active' => true,
                'max_uses' => null,
                'current_uses' => 0,
                'max_uses_per_customer' => 1,
                'online_only' => true,
                'new_customers_only' => false,
                'is_public' => true,
                'requires_code' => false,
                'auto_apply' => true,
                'priority' => 5,
                'stackable' => true,
            ],
            [
                'code' => 'LONGDUR50',
                'name' => 'Engagement Long',
                'description' => '50€ de réduction pour un engagement de 12 mois minimum',
                'discount_type' => 'fixed_amount',
                'discount_value' => 50.00,
                'free_months' => 0,
                'min_duration_months' => 12,
                'min_amount' => 100.00,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => now()->addYear(),
                'is_active' => true,
                'max_uses' => 200,
                'current_uses' => 0,
                'max_uses_per_customer' => 1,
                'online_only' => false,
                'new_customers_only' => false,
                'is_public' => true,
                'requires_code' => true,
                'auto_apply' => false,
                'priority' => 7,
                'stackable' => true,
            ],
            [
                'code' => '1ERMOIS',
                'name' => 'Premier Mois Offert',
                'description' => 'Premier mois gratuit pour tout nouveau client',
                'discount_type' => 'first_month_free',
                'discount_value' => 0,
                'free_months' => 1,
                'min_duration_months' => 6,
                'min_amount' => null,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
                'is_active' => true,
                'max_uses' => 30,
                'current_uses' => 0,
                'max_uses_per_customer' => 1,
                'online_only' => true,
                'new_customers_only' => true,
                'is_public' => true,
                'requires_code' => true,
                'auto_apply' => false,
                'priority' => 9,
                'stackable' => false,
            ],
        ];

        foreach ($promotions as $promo) {
            Promotion::create($promo);
        }

        $this->command->info('5 promotions créées avec succès !');
    }
}
