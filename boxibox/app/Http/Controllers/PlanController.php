<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(Request $request): Response
    {
        // Get tenant_id (for multi-tenant)
        $tenantId = 1; // TODO: Get from authenticated user's tenant

        // Get the selected site or the first site
        $siteId = $request->input('site_id');

        if (!$siteId) {
            $firstSite = Site::where('tenant_id', $tenantId)->first();
            $siteId = $firstSite ? $firstSite->id : null;
        }

        // Get all sites for the dropdown
        $sites = Site::where('tenant_id', $tenantId)->get();

        if (!$siteId) {
            return Inertia::render('Plan/Index', [
                'sites' => $sites,
                'selectedSite' => null,
                'buildings' => [],
                'stats' => [
                    'totalBoxes' => 0,
                    'occupiedBoxes' => 0,
                    'availableBoxes' => 0,
                ],
            ]);
        }

        // Get selected site with buildings, floors, and boxes
        $selectedSite = Site::with([
            'buildings' => function ($query) {
                $query->orderBy('display_order');
            },
            'buildings.floors' => function ($query) {
                $query->orderBy('display_order');
            },
            'buildings.floors.boxes' => function ($query) {
                $query->with(['currentContract.customer']);
            }
        ])->find($siteId);

        // Calculate statistics
        $totalBoxes = Box::whereHas('floor.building.site', function ($query) use ($siteId) {
            $query->where('id', $siteId);
        })->count();

        $occupiedBoxes = Box::whereHas('floor.building.site', function ($query) use ($siteId) {
            $query->where('id', $siteId);
        })->where('status', 'occupied')->count();

        $availableBoxes = $totalBoxes - $occupiedBoxes;

        return Inertia::render('Plan/Index', [
            'sites' => $sites,
            'selectedSite' => $selectedSite,
            'buildings' => $selectedSite->buildings,
            'stats' => [
                'totalBoxes' => $totalBoxes,
                'occupiedBoxes' => $occupiedBoxes,
                'availableBoxes' => $availableBoxes,
            ],
        ]);
    }
}
