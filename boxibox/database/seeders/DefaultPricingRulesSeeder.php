<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use Illuminate\Database\Seeder;

class DefaultPricingRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📊 Création des règles de tarification par défaut...');

        $rules = [
            // Règle 1: Faible occupation (<70%) - Prix attractifs
            [
                'name' => 'Faible Occupation - Prix Attractif',
                'description' => 'Réduction de 10% si l\'occupation est inférieure à 70%',
                'occupancy_threshold_min' => 0,
                'occupancy_threshold_max' => 70,
                'season' => 'all',
                'adjustment_type' => 'percentage',
                'adjustment_value' => -10, // -10%
                'priority' => 100,
                'is_active' => true,
            ],

            // Règle 2: Occupation normale (70-85%) - Prix standard
            [
                'name' => 'Occupation Normale - Prix Standard',
                'description' => 'Pas d\'ajustement si l\'occupation est entre 70% et 85%',
                'occupancy_threshold_min' => 70,
                'occupancy_threshold_max' => 85,
                'season' => 'all',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 0, // 0%
                'priority' => 90,
                'is_active' => true,
            ],

            // Règle 3: Forte occupation (>85%) - Prix premium
            [
                'name' => 'Forte Occupation - Prix Premium',
                'description' => 'Augmentation de 20% si l\'occupation est supérieure à 85%',
                'occupancy_threshold_min' => 85,
                'occupancy_threshold_max' => 100,
                'season' => 'all',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 20, // +20%
                'priority' => 110,
                'is_active' => true,
            ],

            // Règle 4: Haute saison été - Augmentation
            [
                'name' => 'Haute Saison Été',
                'description' => 'Augmentation de 10% pendant l\'été (forte demande)',
                'season' => 'summer',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 10, // +10%
                'priority' => 50,
                'is_active' => true,
            ],

            // Règle 5: Basse saison hiver - Réduction
            [
                'name' => 'Basse Saison Hiver',
                'description' => 'Réduction de 5% pendant l\'hiver (faible demande)',
                'season' => 'winter',
                'adjustment_type' => 'percentage',
                'adjustment_value' => -5, // -5%
                'priority' => 50,
                'is_active' => true,
            ],

            // Règle 6: Engagement 6 mois - Réduction fidélité
            [
                'name' => 'Engagement 6 Mois',
                'description' => 'Réduction de 5% pour un engagement de 6 mois minimum',
                'duration_months_min' => 6,
                'season' => 'all',
                'adjustment_type' => 'percentage',
                'adjustment_value' => -5, // -5%
                'priority' => 40,
                'is_active' => true,
            ],

            // Règle 7: Engagement 12 mois - Réduction fidélité renforcée
            [
                'name' => 'Engagement 12 Mois',
                'description' => 'Réduction de 10% pour un engagement de 12 mois minimum',
                'duration_months_min' => 12,
                'season' => 'all',
                'adjustment_type' => 'percentage',
                'adjustment_value' => -10, // -10%
                'priority' => 45,
                'is_active' => true,
            ],

            // Règle 8: Grands boxes (>15m³) en hiver - Double discount
            [
                'name' => 'Grands Boxes Hiver',
                'description' => 'Réduction supplémentaire de 10% pour grands boxes en hiver',
                'box_size_min' => 15,
                'season' => 'winter',
                'adjustment_type' => 'percentage',
                'adjustment_value' => -10, // -10%
                'priority' => 30,
                'is_active' => true,
            ],

            // Règle 9: Petits boxes (<8m³) haute occupation - Premium
            [
                'name' => 'Petits Boxes Forte Demande',
                'description' => 'Augmentation de 15% pour petits boxes si occupation > 90%',
                'box_size_max' => 8,
                'occupancy_threshold_min' => 90,
                'season' => 'all',
                'adjustment_type' => 'percentage',
                'adjustment_value' => 15, // +15%
                'priority' => 120,
                'is_active' => true,
            ],

            // Règle 10: Promotion automne - Rentrée
            [
                'name' => 'Promotion Rentrée Automne',
                'description' => 'Offre spéciale rentrée - 8% de réduction en automne',
                'season' => 'fall',
                'adjustment_type' => 'percentage',
                'adjustment_value' => -8, // -8%
                'priority' => 55,
                'is_active' => true,
                'valid_from' => now()->startOfYear()->addMonths(8), // Septembre
                'valid_until' => now()->startOfYear()->addMonths(10), // Novembre
            ],
        ];

        foreach ($rules as $ruleData) {
            PricingRule::create($ruleData);
        }

        $this->command->info('   ✓ ' . count($rules) . ' règles de tarification créées');

        $this->command->newLine();
        $this->command->table(
            ['Règle', 'Ajustement', 'Priorité', 'Condition'],
            collect($rules)->map(function ($rule) {
                return [
                    $rule['name'],
                    $rule['adjustment_value'] . '%',
                    $rule['priority'],
                    $this->getConditionSummary($rule),
                ];
            })->toArray()
        );
    }

    private function getConditionSummary(array $rule): string
    {
        $conditions = [];

        if (isset($rule['occupancy_threshold_min']) || isset($rule['occupancy_threshold_max'])) {
            $min = $rule['occupancy_threshold_min'] ?? 0;
            $max = $rule['occupancy_threshold_max'] ?? 100;
            $conditions[] = "Occupation {$min}-{$max}%";
        }

        if ($rule['season'] !== 'all') {
            $conditions[] = ucfirst($rule['season']);
        }

        if (isset($rule['duration_months_min'])) {
            $conditions[] = "≥{$rule['duration_months_min']} mois";
        }

        if (isset($rule['box_size_min']) || isset($rule['box_size_max'])) {
            if (isset($rule['box_size_min'])) {
                $conditions[] = "≥{$rule['box_size_min']}m³";
            }
            if (isset($rule['box_size_max'])) {
                $conditions[] = "≤{$rule['box_size_max']}m³";
            }
        }

        return implode(', ', $conditions) ?: 'Toujours';
    }
}
