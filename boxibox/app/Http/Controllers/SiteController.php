<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = 1; // TODO: Get from authenticated user's tenant

        $sites = Site::where('tenant_id', $tenantId)
            ->withCount(['buildings', 'boxes'])
            ->paginate(15)
            ->through(function ($site) {
                $totalBoxes = $site->boxes()->count();
                $occupiedBoxes = $site->boxes()->where('status', 'occupied')->count();
                $site->occupancy_rate = $totalBoxes > 0 ? round(($occupiedBoxes / $totalBoxes) * 100, 1) : 0;
                return $site;
            });

        return Inertia::render('Sites/Index', [
            'sites' => $sites,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Sites/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gps_latitude' => 'nullable|numeric',
            'gps_longitude' => 'nullable|numeric',
        ]);

        $validated['tenant_id'] = 1; // TODO: Get from authenticated user's tenant
        $validated['slug'] = Str::slug($validated['name']);

        Site::create($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site créé avec succès.');
    }

    public function show(Site $site): Response
    {
        $site->load([
            'buildings.floors.boxes',
            'boxes.currentContract.customer',
        ]);

        return Inertia::render('Sites/Show', [
            'site' => $site,
        ]);
    }

    public function edit(Site $site): Response
    {
        return Inertia::render('Sites/Edit', [
            'site' => $site,
        ]);
    }

    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gps_latitude' => 'nullable|numeric',
            'gps_longitude' => 'nullable|numeric',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $site->update($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site modifié avec succès.');
    }

    public function destroy(Site $site)
    {
        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Site supprimé avec succès.');
    }
}
