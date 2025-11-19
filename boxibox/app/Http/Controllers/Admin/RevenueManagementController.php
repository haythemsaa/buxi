<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\DynamicPricingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RevenueManagementController extends Controller
{
    public function __construct(
        private DynamicPricingService $pricingService
    ) {}

    /**
     * Display revenue management dashboard
     */
    public function index(Request $request)
    {
        $siteId = $request->get('site_id');
        $sites = Site::all();

        if (!$siteId && $sites->isNotEmpty()) {
            $siteId = $sites->first()->id;
        }

        $site = $siteId ? Site::findOrFail($siteId) : null;

        $data = [
            'sites' => $sites,
            'selected_site_id' => $siteId,
        ];

        if ($site) {
            $data['revenue_gap'] = $this->pricingService->getRevenueGap($site);
            $data['occupancy_rate'] = $this->pricingService->getOccupancyRate($site->id);
            $data['recommendations'] = $this->pricingService->getPricingRecommendations($site);
        }

        return Inertia::render('Admin/RevenueManagement/Dashboard', $data);
    }

    /**
     * Update all prices for a site
     */
    public function updatePrices(Request $request, Site $site)
    {
        $updated = $this->pricingService->updateSitePrices($site);

        return back()->with('success', "{$updated} prix mis à jour pour le site {$site->name}.");
    }

    /**
     * Get price simulation
     */
    public function simulate(Request $request, Site $site)
    {
        $validated = $request->validate([
            'percentage_change' => 'required|numeric|min:-50|max:100',
        ]);

        $simulation = $this->pricingService->simulatePriceChange(
            $site,
            $validated['percentage_change']
        );

        return response()->json($simulation);
    }

    /**
     * Get revenue analytics
     */
    public function analytics(Request $request, Site $site)
    {
        return response()->json([
            'revenue_gap' => $this->pricingService->getRevenueGap($site),
            'occupancy_rate' => $this->pricingService->getOccupancyRate($site->id),
            'recommendations' => $this->pricingService->getPricingRecommendations($site, 20),
        ]);
    }
}
