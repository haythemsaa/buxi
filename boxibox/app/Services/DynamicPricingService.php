<?php

namespace App\Services;

use App\Models\Box;
use App\Models\Site;
use App\Models\PricingRule;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DynamicPricingService
{
    /**
     * Calculate optimal price for a box
     */
    public function calculateOptimalPrice(
        Box $box,
        int $durationMonths = null,
        bool $applyRules = true
    ): float {
        // Get base price
        $basePrice = $box->base_price_monthly_ht ?? $box->price_monthly_ht;

        if (!$applyRules || !$box->use_dynamic_pricing) {
            return $basePrice;
        }

        // Get current occupancy rate for the site
        $occupancyRate = $this->getOccupancyRate($box->floor->building->site_id);

        // Get applicable pricing rules
        $rules = PricingRule::applicableToBox($box, $occupancyRate, $durationMonths)->get();

        // Apply all rules in priority order
        $finalPrice = $basePrice;
        foreach ($rules as $rule) {
            $finalPrice = $rule->applyToPrice($finalPrice);

            Log::info("Applied pricing rule '{$rule->name}' to box {$box->id}", [
                'base_price' => $basePrice,
                'adjustment' => $rule->adjustment_value,
                'type' => $rule->adjustment_type,
                'result' => $finalPrice,
            ]);
        }

        // Ensure price doesn't go below minimum (e.g., 50% of base)
        $minPrice = $basePrice * 0.5;
        $finalPrice = max($finalPrice, $minPrice);

        // Round to 2 decimals
        return round($finalPrice, 2);
    }

    /**
     * Get occupancy rate for a site
     */
    public function getOccupancyRate(int $siteId): float
    {
        return Cache::remember("occupancy_rate_{$siteId}", 300, function () use ($siteId) {
            $site = Site::findOrFail($siteId);

            $total = DB::table('boxes')
                ->join('floors', 'boxes.floor_id', '=', 'floors.id')
                ->join('buildings', 'floors.building_id', '=', 'buildings.id')
                ->where('buildings.site_id', $siteId)
                ->count();

            if ($total === 0) {
                return 0.0;
            }

            $rented = DB::table('boxes')
                ->join('floors', 'boxes.floor_id', '=', 'floors.id')
                ->join('buildings', 'floors.building_id', '=', 'buildings.id')
                ->where('buildings.site_id', $siteId)
                ->where('boxes.status', 'rented')
                ->count();

            return round(($rented / $total) * 100, 2);
        });
    }

    /**
     * Update all boxes prices for a site
     */
    public function updateSitePrices(Site $site): int
    {
        $updated = 0;
        $occupancyRate = $this->getOccupancyRate($site->id);

        foreach ($site->boxes as $box) {
            if ($box->use_dynamic_pricing && $box->status === 'available') {
                $optimalPrice = $this->calculateOptimalPrice($box);

                if ($box->current_optimal_price !== $optimalPrice) {
                    $box->update([
                        'current_optimal_price' => $optimalPrice,
                        'price_last_updated' => now(),
                    ]);
                    $updated++;
                }
            }
        }

        Log::info("Updated prices for site {$site->id}", [
            'boxes_updated' => $updated,
            'occupancy_rate' => $occupancyRate,
        ]);

        return $updated;
    }

    /**
     * Get revenue gap analysis for a site
     */
    public function getRevenueGap(Site $site): array
    {
        $contracts = Contract::whereHas('box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->where('status', 'active')->get();

        $currentRevenue = $contracts->sum('price_monthly_ht');

        // Calculate max potential revenue (all boxes rented at optimal price)
        $maxRevenue = 0;
        foreach ($site->boxes as $box) {
            $optimalPrice = $this->calculateOptimalPrice($box);
            $maxRevenue += $optimalPrice;
        }

        $gap = $maxRevenue - $currentRevenue;
        $efficiency = $maxRevenue > 0 ? ($currentRevenue / $maxRevenue) * 100 : 0;

        return [
            'current_mrr' => round($currentRevenue, 2),
            'max_potential_mrr' => round($maxRevenue, 2),
            'gap_mrr' => round($gap, 2),
            'efficiency_percentage' => round($efficiency, 2),
            'annual_current' => round($currentRevenue * 12, 2),
            'annual_potential' => round($maxRevenue * 12, 2),
            'annual_gap' => round($gap * 12, 2),
        ];
    }

    /**
     * Get pricing recommendations for available boxes
     */
    public function getPricingRecommendations(Site $site, int $limit = 10): array
    {
        $recommendations = [];

        $boxes = Box::whereHas('floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })
        ->where('status', 'available')
        ->where('use_dynamic_pricing', true)
        ->get();

        foreach ($boxes as $box) {
            $currentPrice = $box->price_monthly_ht;
            $optimalPrice = $this->calculateOptimalPrice($box);
            $difference = $optimalPrice - $currentPrice;
            $percentageChange = $currentPrice > 0 ? ($difference / $currentPrice) * 100 : 0;

            if (abs($percentageChange) >= 5) { // Only recommend if change >= 5%
                $recommendations[] = [
                    'box_id' => $box->id,
                    'box_number' => $box->number,
                    'current_price' => round($currentPrice, 2),
                    'recommended_price' => round($optimalPrice, 2),
                    'difference' => round($difference, 2),
                    'percentage_change' => round($percentageChange, 2),
                    'action' => $difference > 0 ? 'increase' : 'decrease',
                    'reason' => $this->getRecommendationReason($box, $optimalPrice),
                ];
            }
        }

        // Sort by absolute percentage change (highest first)
        usort($recommendations, function ($a, $b) {
            return abs($b['percentage_change']) <=> abs($a['percentage_change']);
        });

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Get reason for price recommendation
     */
    private function getRecommendationReason(Box $box, float $optimalPrice): string
    {
        $occupancyRate = $this->getOccupancyRate($box->floor->building->site_id);

        if ($occupancyRate < 70) {
            return "Faible occupation ({$occupancyRate}%) - prix attractif recommandé";
        }

        if ($occupancyRate > 85) {
            return "Forte occupation ({$occupancyRate}%) - prix premium recommandé";
        }

        $season = $this->getCurrentSeason();
        if (in_array($season, ['summer', 'fall'])) {
            return "Haute saison ({$season}) - augmentation recommandée";
        }

        return "Optimisation basée sur les règles actives";
    }

    /**
     * Simulate price change impact
     */
    public function simulatePriceChange(Site $site, float $percentageChange): array
    {
        $currentMRR = Contract::whereHas('box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->where('status', 'active')->sum('price_monthly_ht');

        // Simple elastic demand model
        // Assume: 1% price increase = 0.5% demand decrease
        $demandElasticity = -0.5;
        $demandChange = $percentageChange * $demandElasticity;

        $newMRR = $currentMRR * (1 + $percentageChange / 100) * (1 + $demandChange / 100);

        return [
            'current_mrr' => round($currentMRR, 2),
            'price_change_percentage' => $percentageChange,
            'estimated_demand_change' => round($demandChange, 2),
            'projected_mrr' => round($newMRR, 2),
            'projected_impact' => round($newMRR - $currentMRR, 2),
            'annual_impact' => round(($newMRR - $currentMRR) * 12, 2),
        ];
    }

    /**
     * Get current season
     */
    private function getCurrentSeason(): string
    {
        $month = now()->month;

        return match (true) {
            in_array($month, [12, 1, 2]) => 'winter',
            in_array($month, [3, 4, 5]) => 'spring',
            in_array($month, [6, 7, 8]) => 'summer',
            in_array($month, [9, 10, 11]) => 'fall',
        };
    }

    /**
     * Get pricing history for analytics
     */
    public function getPricingHistory(Box $box, int $days = 30): array
    {
        // This would require a pricing_history table to track price changes over time
        // For now, return simple data
        return [
            'box_id' => $box->id,
            'current_price' => $box->current_optimal_price ?? $box->price_monthly_ht,
            'base_price' => $box->base_price_monthly_ht ?? $box->price_monthly_ht,
            'last_updated' => $box->price_last_updated,
        ];
    }
}
