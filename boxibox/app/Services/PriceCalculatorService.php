<?php

namespace App\Services;

use App\Models\Box;
use App\Models\Promotion;
use App\Models\PriceRule;

class PriceCalculatorService
{
    /**
     * Calculer le prix pour une réservation
     */
    public function calculatePrice(Box $box, int $duration_months = 1, ?Promotion $promotion = null, array $options = []): array
    {
        $base_price = $box->price_monthly_ht;
        $tax_rate = 20.00; // TVA française

        // Appliquer les règles de prix automatiques
        $price_after_rules = $this->applyPriceRules($base_price, $box, $duration_months);

        // Appliquer la promotion
        $discount = 0;
        if ($promotion && $promotion->isValid()) {
            $discount = $promotion->calculateDiscount($price_after_rules, $duration_months);
        }

        $final_price_ht = $price_after_rules - $discount;

        // Assurance
        $insurance = $options['insurance'] ?? false;
        $insurance_monthly = $insurance ? $this->calculateInsurance($box) : 0;

        // Dépôt de garantie (1 mois de loyer)
        $deposit = $final_price_ht;

        // Calculs
        $subtotal_ht = $final_price_ht + $insurance_monthly;
        $tax_amount = $subtotal_ht * ($tax_rate / 100);
        $total_ttc = $subtotal_ht + $tax_amount;

        // Premier paiement = 1er mois + dépôt
        $first_payment = $total_ttc + $deposit;

        return [
            'monthly_price_ht' => $final_price_ht,
            'base_price_ht' => $base_price,
            'price_after_rules' => $price_after_rules,
            'discount_amount' => $discount,
            'insurance_monthly' => $insurance_monthly,
            'subtotal_ht' => $subtotal_ht,
            'tax_rate' => $tax_rate,
            'tax_amount' => $tax_amount,
            'total_monthly_ttc' => $total_ttc,
            'deposit_amount' => $deposit,
            'first_payment' => $first_payment,
            'total_for_duration' => $total_ttc * $duration_months,
            'breakdown' => [
                'location' => $final_price_ht,
                'insurance' => $insurance_monthly,
                'tax' => $tax_amount,
                'deposit' => $deposit,
            ],
        ];
    }

    /**
     * Appliquer les règles de prix automatiques
     */
    private function applyPriceRules(float $base_price, Box $box, int $duration): float
    {
        $price = $base_price;

        // Réduction pour durée longue
        if ($duration >= 12) {
            $price *= 0.90; // -10%
        } elseif ($duration >= 6) {
            $price *= 0.95; // -5%
        } elseif ($duration >= 3) {
            $price *= 0.98; // -2%
        }

        // Prix dynamiques basés sur l'occupation
        $occupancy_rate = $this->getOccupancyRate($box->floor->building);
        if ($occupancy_rate > 90) {
            $price *= 1.05; // +5% si très forte demande
        } elseif ($occupancy_rate < 60) {
            $price *= 0.95; // -5% si faible demande
        }

        return round($price, 2);
    }

    /**
     * Calculer le prix de l'assurance
     */
    private function calculateInsurance(Box $box): float
    {
        // Assurance basée sur le volume du box
        $volume = $box->volume;

        if ($volume < 5) {
            return 15.00; // Petit box
        } elseif ($volume < 10) {
            return 25.00; // Moyen
        } elseif ($volume < 20) {
            return 35.00; // Grand
        } else {
            return 50.00; // Très grand
        }
    }

    /**
     * Obtenir le taux d'occupation
     */
    private function getOccupancyRate($building): float
    {
        $total_boxes = $building->floors->sum(function($floor) {
            return $floor->boxes->count();
        });

        $occupied_boxes = $building->floors->sum(function($floor) {
            return $floor->boxes->where('status', 'occupied')->count();
        });

        return $total_boxes > 0 ? ($occupied_boxes / $total_boxes) * 100 : 0;
    }

    /**
     * Comparer plusieurs boxes
     */
    public function compareBoxes(array $box_ids, int $duration_months = 1): array
    {
        $comparisons = [];

        foreach ($box_ids as $box_id) {
            $box = Box::with(['floor.building.site'])->find($box_id);
            if (!$box) continue;

            $pricing = $this->calculatePrice($box, $duration_months);

            $comparisons[] = [
                'box_id' => $box->id,
                'box_number' => $box->number,
                'volume' => $box->volume,
                'surface' => $box->surface,
                'dimensions' => "{$box->length}x{$box->width}x{$box->height}",
                'floor' => $box->floor->name,
                'building' => $box->floor->building->name,
                'site' => $box->floor->building->site->name,
                'site_address' => $box->floor->building->site->address,
                'features' => [
                    'climate_controlled' => $box->climate_controlled,
                    'ground_floor' => $box->ground_floor,
                    'vehicle_access' => $box->vehicle_access,
                    'has_electricity' => $box->has_electricity,
                ],
                'pricing' => $pricing,
                'score' => $this->calculateBoxScore($box, $pricing),
            ];
        }

        // Trier par score
        usort($comparisons, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $comparisons;
    }

    /**
     * Calculer un score de recommandation
     */
    private function calculateBoxScore(Box $box, array $pricing): float
    {
        $score = 0;

        // Rapport qualité/prix
        $price_per_m3 = $pricing['monthly_price_ht'] / $box->volume;
        $score += (100 / $price_per_m3); // Plus c'est bas, mieux c'est

        // Fonctionnalités
        if ($box->climate_controlled) $score += 10;
        if ($box->ground_floor) $score += 5;
        if ($box->vehicle_access) $score += 5;
        if ($box->has_electricity) $score += 3;

        return round($score, 2);
    }

    /**
     * Valider un code promo
     */
    public function validatePromoCode(string $code, ?int $customer_id = null): ?Promotion
    {
        $promotion = Promotion::where('code', strtoupper($code))
            ->active()
            ->first();

        if (!$promotion) {
            return null;
        }

        // Vérifier les utilisations du client
        if ($customer_id && $promotion->max_uses_per_customer > 0) {
            $customer_uses = \App\Models\Reservation::where('customer_id', $customer_id)
                ->where('promotion_id', $promotion->id)
                ->count();

            if ($customer_uses >= $promotion->max_uses_per_customer) {
                return null;
            }
        }

        return $promotion;
    }
}
