<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteManagementController extends Controller
{
    /**
     * Display a listing of sites
     */
    public function index()
    {
        $sites = Site::withCount(['buildings', 'boxes'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Sites/Index', [
            'sites' => $sites,
        ]);
    }

    /**
     * Show the form for creating a new site
     */
    public function create()
    {
        return Inertia::render('Admin/Sites/Create');
    }

    /**
     * Store a newly created site
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,maintenance',
            'plan_enabled' => 'boolean',
        ]);

        $site = Site::create($validated);

        return redirect()->route('admin.sites.show', $site)
            ->with('success', 'Site créé avec succès.');
    }

    /**
     * Display the specified site
     */
    public function show(Site $site)
    {
        $site->load([
            'buildings' => function ($query) {
                $query->withCount('floors')->orderBy('display_order');
            }
        ]);

        $stats = [
            'total_buildings' => $site->buildings->count(),
            'total_floors' => $site->buildings->sum('floors_count'),
            'total_boxes' => $site->boxes()->count(),
            'available_boxes' => $site->boxes()->where('status', 'available')->count(),
            'occupied_boxes' => $site->boxes()->where('status', 'occupied')->count(),
        ];

        return Inertia::render('Admin/Sites/Show', [
            'site' => $site,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified site
     */
    public function edit(Site $site)
    {
        return Inertia::render('Admin/Sites/Edit', [
            'site' => $site,
        ]);
    }

    /**
     * Update the specified site
     */
    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,maintenance',
            'plan_enabled' => 'boolean',
        ]);

        $site->update($validated);

        return redirect()->route('admin.sites.show', $site)
            ->with('success', 'Site mis à jour avec succès.');
    }

    /**
     * Remove the specified site
     */
    public function destroy(Site $site)
    {
        // Check if site has buildings
        if ($site->buildings()->exists()) {
            return back()->with('error', 'Impossible de supprimer un site contenant des bâtiments.');
        }

        $site->delete();

        return redirect()->route('admin.sites.index')
            ->with('success', 'Site supprimé avec succès.');
    }
}
