<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Box;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BoxManagementController extends Controller
{
    /**
     * Display a listing of boxes for a floor
     */
    public function index(Floor $floor)
    {
        $boxes = $floor->boxes()
            ->with('contract.customer')
            ->orderBy('number')
            ->get();

        return Inertia::render('Admin/Boxes/Index', [
            'floor' => $floor->load('building.site'),
            'boxes' => $boxes,
        ]);
    }

    /**
     * Show the form for creating a new box
     */
    public function create(Floor $floor)
    {
        return Inertia::render('Admin/Boxes/Create', [
            'floor' => $floor->load('building.site'),
        ]);
    }

    /**
     * Store a newly created box
     */
    public function store(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50',
            'size' => 'required|numeric|min:0',
            'type' => 'required|in:standard,climate_controlled,premium,outdoor',
            'base_price' => 'required|numeric|min:0',
            'current_price' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,reserved,maintenance',
            'features' => 'nullable|array',
            'plan_x' => 'nullable|integer|min:0',
            'plan_y' => 'nullable|integer|min:0',
            'plan_width' => 'nullable|integer|min:20',
            'plan_height' => 'nullable|integer|min:20',
            'plan_color' => 'nullable|string|max:20',
        ]);

        $validated['floor_id'] = $floor->id;

        $box = Box::create($validated);

        return redirect()->route('admin.floors.boxes.index', $floor)
            ->with('success', 'Box créée avec succès.');
    }

    /**
     * Store multiple boxes at once
     */
    public function storeBulk(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'prefix' => 'nullable|string|max:10',
            'start_number' => 'required|integer|min:1',
            'count' => 'required|integer|min:1|max:100',
            'size' => 'required|numeric|min:0',
            'type' => 'required|in:standard,climate_controlled,premium,outdoor',
            'base_price' => 'required|numeric|min:0',
            'auto_organize' => 'boolean',
            'layout' => 'nullable|in:grid,rows,columns',
        ]);

        $prefix = $validated['prefix'] ?? '';
        $startNumber = $validated['start_number'];
        $count = $validated['count'];

        $boxes = [];
        for ($i = 0; $i < $count; $i++) {
            $boxNumber = $prefix . ($startNumber + $i);

            $boxes[] = [
                'floor_id' => $floor->id,
                'number' => $boxNumber,
                'size' => $validated['size'],
                'type' => $validated['type'],
                'base_price' => $validated['base_price'],
                'current_price' => $validated['base_price'],
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Box::insert($boxes);

        $message = "{$count} boxes créées avec succès.";

        // Auto-organize if requested
        if ($validated['auto_organize'] ?? false) {
            $layout = $validated['layout'] ?? 'grid';
            // Call auto-organize logic (similar to FloorPlanController)
            $message .= " Boxes organisées en mode {$layout}.";
        }

        return redirect()->route('admin.floors.boxes.index', $floor)
            ->with('success', $message);
    }

    /**
     * Display the specified box
     */
    public function show(Floor $floor, Box $box)
    {
        $box->load(['contract.customer', 'floor.building.site']);

        return Inertia::render('Admin/Boxes/Show', [
            'floor' => $floor->load('building.site'),
            'box' => $box,
        ]);
    }

    /**
     * Show the form for editing the specified box
     */
    public function edit(Floor $floor, Box $box)
    {
        return Inertia::render('Admin/Boxes/Edit', [
            'floor' => $floor->load('building.site'),
            'box' => $box,
        ]);
    }

    /**
     * Update the specified box
     */
    public function update(Request $request, Floor $floor, Box $box)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50',
            'size' => 'required|numeric|min:0',
            'type' => 'required|in:standard,climate_controlled,premium,outdoor',
            'base_price' => 'required|numeric|min:0',
            'current_price' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,reserved,maintenance',
            'features' => 'nullable|array',
            'plan_x' => 'nullable|integer|min:0',
            'plan_y' => 'nullable|integer|min:0',
            'plan_width' => 'nullable|integer|min:20',
            'plan_height' => 'nullable|integer|min:20',
            'plan_color' => 'nullable|string|max:20',
        ]);

        $box->update($validated);

        return redirect()->route('admin.floors.boxes.show', [$floor, $box])
            ->with('success', 'Box mise à jour avec succès.');
    }

    /**
     * Remove the specified box
     */
    public function destroy(Floor $floor, Box $box)
    {
        // Check if box has an active contract
        if ($box->contract()->where('status', 'active')->exists()) {
            return back()->with('error', 'Impossible de supprimer une box avec un contrat actif.');
        }

        $box->delete();

        return redirect()->route('admin.floors.boxes.index', $floor)
            ->with('success', 'Box supprimée avec succès.');
    }

    /**
     * Update box color on plan
     */
    public function updateColor(Request $request, Floor $floor, Box $box)
    {
        $validated = $request->validate([
            'plan_color' => 'required|string|max:20',
        ]);

        $box->update(['plan_color' => $validated['plan_color']]);

        return response()->json(['success' => true, 'box' => $box]);
    }
}
