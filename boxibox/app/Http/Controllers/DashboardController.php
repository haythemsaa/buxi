<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        // Get tenant_id (for multi-tenant)
        $tenantId = 1; // TODO: Get from authenticated user's tenant

        // Get all sites for this tenant
        $sites = Site::where('tenant_id', $tenantId)->get();
        $siteIds = $sites->pluck('id');

        // Calculate KPIs
        $kpis = $this->calculateKPIs($siteIds);

        // Get recent activity
        $recentContracts = Contract::with(['customer', 'box'])
            ->whereIn('site_id', $siteIds)
            ->latest()
            ->take(5)
            ->get();

        $pendingInvoices = Invoice::with(['customer', 'contract'])
            ->whereIn('site_id', $siteIds)
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $overdueInvoices = Invoice::with(['customer', 'contract'])
            ->whereIn('site_id', $siteIds)
            ->overdue()
            ->latest()
            ->take(5)
            ->get();

        // Monthly revenue chart data (last 12 months)
        $monthlyRevenue = $this->getMonthlyRevenue($siteIds);

        // Occupancy trend (last 6 months)
        $occupancyTrend = $this->getOccupancyTrend($siteIds);

        return Inertia::render('Dashboard/Index', [
            'kpis' => $kpis,
            'sites' => $sites,
            'recentContracts' => $recentContracts,
            'pendingInvoices' => $pendingInvoices,
            'overdueInvoices' => $overdueInvoices,
            'monthlyRevenue' => $monthlyRevenue,
            'occupancyTrend' => $occupancyTrend,
        ]);
    }

    private function calculateKPIs(array $siteIds): array
    {
        // Total boxes
        $totalBoxes = Box::whereHas('floor.building.site', function ($query) use ($siteIds) {
            $query->whereIn('id', $siteIds);
        })->count();

        // Occupied boxes
        $occupiedBoxes = Box::whereHas('floor.building.site', function ($query) use ($siteIds) {
            $query->whereIn('id', $siteIds);
        })->where('status', 'occupied')->count();

        // Available boxes
        $availableBoxes = Box::whereHas('floor.building.site', function ($query) use ($siteIds) {
            $query->whereIn('id', $siteIds);
        })->where('status', 'available')->count();

        // Occupancy rate
        $occupancyRate = $totalBoxes > 0 ? round(($occupiedBoxes / $totalBoxes) * 100, 2) : 0;

        // Active contracts
        $activeContracts = Contract::whereIn('site_id', $siteIds)
            ->where('status', 'active')
            ->count();

        // Active customers
        $activeCustomers = Customer::whereHas('activeContracts', function ($query) use ($siteIds) {
            $query->whereIn('site_id', $siteIds);
        })->count();

        // Monthly revenue (current month)
        $currentMonthRevenue = Invoice::whereIn('site_id', $siteIds)
            ->whereMonth('issue_date', now()->month)
            ->whereYear('issue_date', now()->year)
            ->sum('total_ttc');

        // Last month revenue
        $lastMonthRevenue = Invoice::whereIn('site_id', $siteIds)
            ->whereMonth('issue_date', now()->subMonth()->month)
            ->whereYear('issue_date', now()->subMonth()->year)
            ->sum('total_ttc');

        // Revenue change percentage
        $revenueChange = $lastMonthRevenue > 0
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2)
            : 0;

        // Pending invoices amount
        $pendingAmount = Invoice::whereIn('site_id', $siteIds)
            ->where('status', 'pending')
            ->sum('total_ttc');

        // Overdue invoices amount
        $overdueAmount = Invoice::whereIn('site_id', $siteIds)
            ->overdue()
            ->sum('total_ttc');

        // Average monthly price
        $avgMonthlyPrice = Contract::whereIn('site_id', $siteIds)
            ->where('status', 'active')
            ->avg('monthly_price');

        // Total volume (m³)
        $totalVolume = Box::whereHas('floor.building.site', function ($query) use ($siteIds) {
            $query->whereIn('id', $siteIds);
        })->sum('volume');

        // Total surface (m²)
        $totalSurface = Box::whereHas('floor.building.site', function ($query) use ($siteIds) {
            $query->whereIn('id', $siteIds);
        })->sum('surface');

        return [
            'totalBoxes' => $totalBoxes,
            'occupiedBoxes' => $occupiedBoxes,
            'availableBoxes' => $availableBoxes,
            'occupancyRate' => $occupancyRate,
            'activeContracts' => $activeContracts,
            'activeCustomers' => $activeCustomers,
            'currentMonthRevenue' => round($currentMonthRevenue, 2),
            'lastMonthRevenue' => round($lastMonthRevenue, 2),
            'revenueChange' => $revenueChange,
            'pendingAmount' => round($pendingAmount, 2),
            'overdueAmount' => round($overdueAmount, 2),
            'avgMonthlyPrice' => round($avgMonthlyPrice, 2),
            'totalVolume' => round($totalVolume, 2),
            'totalSurface' => round($totalSurface, 2),
        ];
    }

    private function getMonthlyRevenue(array $siteIds): array
    {
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $revenue = Invoice::whereIn('site_id', $siteIds)
                ->whereMonth('issue_date', $date->month)
                ->whereYear('issue_date', $date->year)
                ->sum('total_ttc');

            $data[] = [
                'month' => $date->format('M Y'),
                'revenue' => round($revenue, 2),
            ];
        }

        return $data;
    }

    private function getOccupancyTrend(array $siteIds): array
    {
        // Simplified: current occupancy for each month
        // In production, you'd track historical occupancy data
        $data = [];
        $currentOccupancyRate = $this->calculateKPIs($siteIds)['occupancyRate'];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            // Simulate slight variations for demo
            $rate = $currentOccupancyRate + rand(-5, 5);
            $rate = max(0, min(100, $rate)); // Keep between 0-100

            $data[] = [
                'month' => $date->format('M Y'),
                'rate' => round($rate, 2),
            ];
        }

        return $data;
    }
}
