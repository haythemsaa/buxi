<?php

namespace Database\Seeders;

use App\Models\PriceRule;
use App\Models\Site;
use Illuminate\Database\Seeder;

class PriceRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = Site::all();

        if ($sites->isEmpty()) {
            $this->command->warn('No sites found. Please run BoxiboxSeeder first.');
            return;
        }

        $siteIds = $sites->pluck('id')->toArray();

        $priceRules = [
            // Règle 1: Réduction longue durée (12+ mois)
            [
                'name' => 'Réduction Engagement 12 mois',
                'description' => '10% de réduction pour un engagement de 12 mois ou plus',
                'rule_type' => 'duration_discount',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 10.00,
                'min_duration_months' => 12,
                'max_duration_months' => null,
                'min_occupancy_rate' => null,
                'max_occupancy_rate' => null,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'is_active' => true,
                'auto_apply' => true,
                'priority' => 10,
                'stackable' => true,
            ],

            // Règle 2: Réduction occupation faible
            [
                'name' => 'Tarif Occupation Faible',
                'description' => '15% de réduction quand le taux d\'occupation est inférieur à 60%',
                'rule_type' => 'occupancy_based',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 15.00,
                'min_duration_months' => null,
                'max_duration_months' => null,
                'min_occupancy_rate' => 0,
                'max_occupancy_rate' => 60,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'is_active' => true,
                'auto_apply' => true,
                'priority' => 8,
                'stackable' => false,
            ],

            // Règle 3: Supplément haute saison (forte occupation)
            [
                'name' => 'Supplément Forte Demande',
                'description' => '5% de supplément quand le taux d\'occupation dépasse 90%',
                'rule_type' => 'occupancy_based',
                'adjustment_type' => 'percentage',
                'adjustment_value' => -5.00, // Négatif = augmentation
                'min_duration_months' => null,
                'max_duration_months' => null,
                'min_occupancy_rate' => 90,
                'max_occupancy_rate' => 100,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'is_active' => true,
                'auto_apply' => true,
                'priority' => 9,
                'stackable' => false,
            ],

            // Règle 4: Réduction moyenne durée (6-11 mois)
            [
                'name' => 'Réduction Engagement 6 mois',
                'description' => '5% de réduction pour un engagement de 6 à 11 mois',
                'rule_type' => 'duration_discount',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 5.00,
                'min_duration_months' => 6,
                'max_duration_months' => 11,
                'min_occupancy_rate' => null,
                'max_occupancy_rate' => null,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'is_active' => true,
                'auto_apply' => true,
                'priority' => 7,
                'stackable' => true,
            ],

            // Règle 5: Réduction courte durée (3-5 mois)
            [
                'name' => 'Réduction Engagement 3 mois',
                'description' => '2% de réduction pour un engagement de 3 à 5 mois',
                'rule_type' => 'duration_discount',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 2.00,
                'min_duration_months' => 3,
                'max_duration_months' => 5,
                'min_occupancy_rate' => null,
                'max_occupancy_rate' => null,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'is_active' => true,
                'auto_apply' => true,
                'priority' => 6,
                'stackable' => true,
            ],

            // Règle 6: Early bird (réservation anticipée)
            [
                'name' => 'Réservation Anticipée',
                'description' => '5% de réduction pour une réservation plus de 30 jours à l\'avance',
                'rule_type' => 'early_booking',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 5.00,
                'min_duration_months' => null,
                'max_duration_months' => null,
                'min_occupancy_rate' => null,
                'max_occupancy_rate' => null,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(6),
                'is_active' => true,
                'auto_apply' => false,
                'priority' => 5,
                'stackable' => true,
            ],

            // Règle 7: Boxes XL uniquement
            [
                'name' => 'Promotion Boxes XL',
                'description' => '10% de réduction sur les boxes XL',
                'rule_type' => 'box_type',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 10.00,
                'min_duration_months' => null,
                'max_duration_months' => null,
                'min_occupancy_rate' => null,
                'max_occupancy_rate' => null,
                'applicable_box_types' => ['xl'],
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
                'is_active' => true,
                'auto_apply' => true,
                'priority' => 4,
                'stackable' => true,
            ],

            // Règle 8: Site spécifique (exemple pour le premier site)
            [
                'name' => 'Promotion Site Paris Nord',
                'description' => '15% de réduction pour le site Paris Nord',
                'rule_type' => 'site_specific',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 15.00,
                'min_duration_months' => 3,
                'max_duration_months' => null,
                'min_occupancy_rate' => null,
                'max_occupancy_rate' => null,
                'applicable_box_types' => null,
                'applicable_sites' => [$siteIds[0] ?? 1],
                'valid_from' => now(),
                'valid_until' => now()->addMonths(2),
                'is_active' => true,
                'auto_apply' => true,
                'priority' => 6,
                'stackable' => true,
            ],

            // Règle 9: Réduction fixe haute durée
            [
                'name' => 'Bonus Fidélité 18 mois',
                'description' => '50€ de réduction pour un engagement de 18 mois ou plus',
                'rule_type' => 'duration_discount',
                'adjustment_type' => 'fixed_amount',
                'adjustment_value' => 50.00,
                'min_duration_months' => 18,
                'max_duration_months' => null,
                'min_occupancy_rate' => null,
                'max_occupancy_rate' => null,
                'applicable_box_types' => null,
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => null,
                'is_active' => true,
                'auto_apply' => true,
                'priority' => 11,
                'stackable' => false,
            ],

            // Règle 10: Boxes climatisées uniquement
            [
                'name' => 'Promotion Boxes Climatisées',
                'description' => '8% de réduction sur toutes les boxes climatisées',
                'rule_type' => 'box_type',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 8.00,
                'min_duration_months' => 1,
                'max_duration_months' => null,
                'min_occupancy_rate' => null,
                'max_occupancy_rate' => null,
                'applicable_box_types' => ['climate_controlled'],
                'applicable_sites' => null,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(4),
                'is_active' => true,
                'auto_apply' => true,
                'priority' => 3,
                'stackable' => true,
            ],
        ];

        foreach ($priceRules as $rule) {
            PriceRule::create($rule);
        }

        $this->command->info('✅ ' . count($priceRules) . ' règles de tarification créées avec succès !');
        $this->command->info('📊 Types de règles:');
        $this->command->info('   - Réductions durée: 5 règles');
        $this->command->info('   - Règles occupation: 2 règles');
        $this->command->info('   - Règles type de box: 2 règles');
        $this->command->info('   - Règle site spécifique: 1 règle');
    }
}
