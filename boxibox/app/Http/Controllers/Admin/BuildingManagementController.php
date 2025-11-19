<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Building;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuildingManagementController extends Controller
{
    /**
     * Display a listing of buildings for a site
     */
    public function index(Site $site)
    {
        $buildings = $site->buildings()
            ->withCount(['floors', 'boxes'])
            ->orderBy('display_order')
            ->get();

        return Inertia::render('Admin/Buildings/Index', [
            'site' => $site,
            'buildings' => $buildings,
        ]);
    }

    /**
     * Show the form for creating a new building
     */
    public function create(Site $site)
    {
        return Inertia::render('Admin/Buildings/Create', [
            'site' => $site,
        ]);
    }

    /**
     * Store a newly created building
     */
    public function store(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'plan_color' => 'nullable|string|max:20',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['site_id'] = $site->id;

        // Auto-assign display order if not provided
        if (!isset($validated['display_order'])) {
            $validated['display_order'] = $site->buildings()->max('display_order') + 1;
        }

        $building = Building::create($validated);

        return redirect()->route('admin.sites.buildings.show', [$site, $building])
            ->with('success', 'Bâtiment créé avec succès.');
    }

    /**
     * Display the specified building
     */
    public function show(Site $site, Building $building)
    {
        $building->load([
            'floors' => function ($query) {
                $query->withCount('boxes')->orderBy('display_order');
            }
        ]);

        $stats = [
            'total_floors' => $building->floors->count(),
            'total_boxes' => $building->boxes()->count(),
            'available_boxes' => $building->boxes()->where('status', 'available')->count(),
            'occupied_boxes' => $building->boxes()->where('status', 'occupied')->count(),
        ];

        return Inertia::render('Admin/Buildings/Show', [
            'site' => $site,
            'building' => $building,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified building
     */
    public function edit(Site $site, Building $building)
    {
        return Inertia::render('Admin/Buildings/Edit', [
            'site' => $site,
            'building' => $building,
        ]);
    }

    /**
     * Update the specified building
     */
    public function update(Request $request, Site $site, Building $building)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'plan_color' => 'nullable|string|max:20',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $building->update($validated);

        return redirect()->route('admin.sites.buildings.show', [$site, $building])
            ->with('success', 'Bâtiment mis à jour avec succès.');
    }

    /**
     * Remove the specified building
     */
    public function destroy(Site $site, Building $building)
    {
        // Check if building has floors
        if ($building->floors()->exists()) {
            return back()->with('error', 'Impossible de supprimer un bâtiment contenant des étages.');
        }

        $building->delete();

        return redirect()->route('admin.sites.buildings.index', $site)
            ->with('success', 'Bâtiment supprimé avec succès.');
    }
}
