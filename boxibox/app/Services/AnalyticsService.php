<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Contract;
use App\Models\Box;
use App\Models\Reservation;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * Get occupancy metrics for a site
     */
    public function getOccupancyMetrics(Site $site, ?Carbon $date = null): array
    {
        $date = $date ?? now();
        $cacheKey = "occupancy_metrics_{$site->id}_{$date->format('Y-m-d')}";

        return Cache::remember($cacheKey, 300, function () use ($site, $date) {
            $boxes = Box::whereHas('floor.building', function ($query) use ($site) {
                $query->where('site_id', $site->id);
            })->get();

            $total = $boxes->count();
            $byStatus = $boxes->groupBy('status')->map->count();

            $available = $byStatus['available'] ?? 0;
            $reserved = $byStatus['reserved'] ?? 0;
            $rented = $byStatus['rented'] ?? 0;
            $maintenance = $byStatus['maintenance'] ?? 0;

            $occupancyRate = $total > 0 ? (($rented / $total) * 100) : 0;

            return [
                'total' => $total,
                'available' => $available,
                'reserved' => $reserved,
                'rented' => $rented,
                'maintenance' => $maintenance,
                'occupancy_rate' => round($occupancyRate, 2),
                'trend' => $this->getOccupancyTrend($site, 12),
                'by_size' => $this->getOccupancyBySize($boxes),
            ];
        });
    }

    /**
     * Get occupancy trend (last N months)
     */
    private function getOccupancyTrend(Site $site, int $months = 12): array
    {
        $trend = [];
        $start = now()->subMonths($months);

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $rate = $this->getHistoricalOccupancyRate($site, $month);

            $trend[] = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->translatedFormat('M Y'),
                'rate' => $rate,
            ];
        }

        return $trend;
    }

    /**
     * Get historical occupancy rate
     */
    private function getHistoricalOccupancyRate(Site $site, Carbon $month): float
    {
        // For now, return current rate
        // In production, this would query historical data
        $boxes = Box::whereHas('floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->get();

        $total = $boxes->count();
        $rented = $boxes->where('status', 'rented')->count();

        return $total > 0 ? round(($rented / $total) * 100, 2) : 0;
    }

    /**
     * Get occupancy by box size
     */
    private function getOccupancyBySize($boxes): array
    {
        $grouped = $boxes->groupBy(function ($box) {
            $size = $box->size_m3;
            if ($size < 5) return '< 5m³';
            if ($size < 10) return '5-10m³';
            if ($size < 15) return '10-15m³';
            return '> 15m³';
        });

        return $grouped->map(function ($group) {
            return [
                'total' => $group->count(),
                'rented' => $group->where('status', 'rented')->count(),
                'rate' => $group->count() > 0
                    ? round(($group->where('status', 'rented')->count() / $group->count()) * 100, 2)
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Get revenue metrics
     */
    public function getRevenueMetrics(Site $site, Carbon $month): array
    {
        $contracts = Contract::whereHas('box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->where('status', 'active')->get();

        $mrr = $contracts->sum('price_monthly_ht');
        $arr = $mrr * 12;

        $totalM3 = Box::whereHas('floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->sum('size_m3');

        $revpaf = $totalM3 > 0 ? ($mrr / $totalM3) : 0;

        // Calculate NOI (Net Operating Income)
        $revenue = $this->getMonthlyRevenue($site, $month);
        $expenses = $this->getMonthlyExpenses($site, $month);
        $noi = $revenue - $expenses;

        return [
            'mrr' => round($mrr, 2),
            'arr' => round($arr, 2),
            'revpaf' => round($revpaf, 2),
            'noi' => round($noi, 2),
            'by_size' => $this->groupRevenueBySizeCategory($contracts),
            'monthly_trend' => $this->getRevenueTrend($site, 12),
        ];
    }

    /**
     * Group revenue by size category
     */
    private function groupRevenueBySizeCategory($contracts): array
    {
        $grouped = $contracts->groupBy(function ($contract) {
            $size = $contract->box->size_m3;
            if ($size < 5) return '< 5m³';
            if ($size < 10) return '5-10m³';
            if ($size < 15) return '10-15m³';
            return '> 15m³';
        });

        return $grouped->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => round($group->sum('price_monthly_ht'), 2),
                'average' => round($group->avg('price_monthly_ht'), 2),
            ];
        })->toArray();
    }

    /**
     * Get monthly revenue
     */
    private function getMonthlyRevenue(Site $site, Carbon $month): float
    {
        return Invoice::whereHas('contract.box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })
        ->whereYear('invoice_date', $month->year)
        ->whereMonth('invoice_date', $month->month)
        ->where('status', 'paid')
        ->sum('total_ht');
    }

    /**
     * Get monthly expenses (placeholder)
     */
    private function getMonthlyExpenses(Site $site, Carbon $month): float
    {
        // In production, this would calculate actual operational expenses
        // For now, estimate at 35% of revenue
        $revenue = $this->getMonthlyRevenue($site, $month);
        return $revenue * 0.35;
    }

    /**
     * Get revenue trend
     */
    private function getRevenueTrend(Site $site, int $months = 12): array
    {
        $trend = [];
        $start = now()->subMonths($months);

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $revenue = $this->getMonthlyRevenue($site, $month);

            $trend[] = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->translatedFormat('M Y'),
                'revenue' => round($revenue, 2),
            ];
        }

        return $trend;
    }

    /**
     * Get conversion funnel
     */
    public function getConversionFunnel(Site $site, Carbon $from, Carbon $to): array
    {
        $reservations = Reservation::whereHas('box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->whereBetween('created_at', [$from, $to])->count();

        $converted = Contract::whereHas('box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->whereBetween('created_at', [$from, $to])->count();

        $conversionRate = $reservations > 0 ? ($converted / $reservations) * 100 : 0;

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'reservations' => $reservations,
            'contracts' => $converted,
            'conversion_rate' => round($conversionRate, 2),
            'abandoned' => $reservations - $converted,
        ];
    }

    /**
     * Get customer lifetime value (LTV)
     */
    public function getCustomerLTV(Site $site): array
    {
        $contracts = Contract::whereHas('box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->with('customer')->get();

        $avgMonthlyRevenue = $contracts->avg('price_monthly_ht');
        $avgContractDuration = $this->getAverageContractDuration($site);
        $ltv = $avgMonthlyRevenue * $avgContractDuration;

        return [
            'average_monthly_revenue' => round($avgMonthlyRevenue, 2),
            'average_duration_months' => round($avgContractDuration, 1),
            'lifetime_value' => round($ltv, 2),
        ];
    }

    /**
     * Get average contract duration
     */
    private function getAverageContractDuration(Site $site): float
    {
        $terminatedContracts = Contract::whereHas('box.floor.building', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })
        ->where('status', 'terminated')
        ->whereNotNull('termination_date')
        ->get();

        if ($terminatedContracts->isEmpty()) {
            return 12; // Default assumption
        }

        $totalMonths = $terminatedContracts->sum(function ($contract) {
            return $contract->start_date->diffInMonths($contract->termination_date);
        });

        return $totalMonths / $terminatedContracts->count();
    }

    /**
     * Get dashboard summary
     */
    public function getDashboardSummary(Site $site): array
    {
        $occupancy = $this->getOccupancyMetrics($site);
        $revenue = $this->getRevenueMetrics($site, now());
        $funnel = $this->getConversionFunnel($site, now()->subMonth(), now());
        $ltv = $this->getCustomerLTV($site);

        return [
            'occupancy' => $occupancy,
            'revenue' => $revenue,
            'funnel' => $funnel,
            'ltv' => $ltv,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
