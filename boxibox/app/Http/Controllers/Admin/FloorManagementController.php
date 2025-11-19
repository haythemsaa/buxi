<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Floor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FloorManagementController extends Controller
{
    /**
     * Display a listing of floors for a building
     */
    public function index(Building $building)
    {
        $floors = $building->floors()
            ->withCount('boxes')
            ->orderBy('display_order')
            ->get();

        return Inertia::render('Admin/Floors/Index', [
            'building' => $building->load('site'),
            'floors' => $floors,
        ]);
    }

    /**
     * Show the form for creating a new floor
     */
    public function create(Building $building)
    {
        return Inertia::render('Admin/Floors/Create', [
            'building' => $building->load('site'),
        ]);
    }

    /**
     * Store a newly created floor
     */
    public function store(Request $request, Building $building)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer',
            'plan_width' => 'nullable|integer|min:400|max:5000',
            'plan_height' => 'nullable|integer|min:300|max:5000',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['building_id'] = $building->id;

        // Auto-assign display order if not provided
        if (!isset($validated['display_order'])) {
            $validated['display_order'] = $building->floors()->max('display_order') + 1;
        }

        $floor = Floor::create($validated);

        return redirect()->route('admin.buildings.floors.show', [$building, $floor])
            ->with('success', 'Étage créé avec succès.');
    }

    /**
     * Display the specified floor
     */
    public function show(Building $building, Floor $floor)
    {
        $floor->load([
            'boxes' => function ($query) {
                $query->orderBy('number');
            }
        ]);

        $stats = [
            'total_boxes' => $floor->boxes->count(),
            'available_boxes' => $floor->boxes->where('status', 'available')->count(),
            'occupied_boxes' => $floor->boxes->where('status', 'occupied')->count(),
            'reserved_boxes' => $floor->boxes->where('status', 'reserved')->count(),
        ];

        return Inertia::render('Admin/Floors/Show', [
            'building' => $building->load('site'),
            'floor' => $floor,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified floor
     */
    public function edit(Building $building, Floor $floor)
    {
        return Inertia::render('Admin/Floors/Edit', [
            'building' => $building->load('site'),
            'floor' => $floor,
        ]);
    }

    /**
     * Update the specified floor
     */
    public function update(Request $request, Building $building, Floor $floor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer',
            'plan_width' => 'nullable|integer|min:400|max:5000',
            'plan_height' => 'nullable|integer|min:300|max:5000',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $floor->update($validated);

        return redirect()->route('admin.buildings.floors.show', [$building, $floor])
            ->with('success', 'Étage mis à jour avec succès.');
    }

    /**
     * Remove the specified floor
     */
    public function destroy(Building $building, Floor $floor)
    {
        // Check if floor has boxes
        if ($floor->boxes()->exists()) {
            return back()->with('error', 'Impossible de supprimer un étage contenant des boxes.');
        }

        $floor->delete();

        return redirect()->route('admin.buildings.floors.index', $building)
            ->with('success', 'Étage supprimé avec succès.');
    }
}
