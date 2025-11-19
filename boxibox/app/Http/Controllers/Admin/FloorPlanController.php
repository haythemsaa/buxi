<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Box;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FloorPlanController extends Controller
{
    /**
     * Display the floor plan editor
     */
    public function index(Request $request)
    {
        $siteId = $request->get('site_id');
        $sites = Site::with(['buildings.floors'])->get();

        if (!$siteId && $sites->isNotEmpty()) {
            $siteId = $sites->first()->id;
        }

        $site = $siteId ? Site::with(['buildings.floors'])->findOrFail($siteId) : null;

        return Inertia::render('Admin/FloorPlan/Index', [
            'sites' => $sites,
            'selected_site' => $site,
        ]);
    }

    /**
     * Show floor plan editor for a specific floor
     */
    public function edit(Floor $floor)
    {
        $floor->load([
            'building.site',
            'boxes' => function ($query) {
                $query->orderBy('number');
            }
        ]);

        return Inertia::render('Admin/FloorPlan/Editor', [
            'floor' => $floor,
            'boxes' => $floor->boxes,
            'building' => $floor->building,
            'site' => $floor->building->site,
        ]);
    }

    /**
     * Update box positions on the floor plan
     */
    public function updateBoxPositions(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'boxes' => 'required|array',
            'boxes.*.id' => 'required|exists:boxes,id',
            'boxes.*.plan_x' => 'required|integer|min:0',
            'boxes.*.plan_y' => 'required|integer|min:0',
            'boxes.*.plan_width' => 'nullable|integer|min:20',
            'boxes.*.plan_height' => 'nullable|integer|min:20',
        ]);

        foreach ($validated['boxes'] as $boxData) {
            Box::where('id', $boxData['id'])
                ->where('floor_id', $floor->id)
                ->update([
                    'plan_x' => $boxData['plan_x'],
                    'plan_y' => $boxData['plan_y'],
                    'plan_width' => $boxData['plan_width'] ?? 100,
                    'plan_height' => $boxData['plan_height'] ?? 100,
                ]);
        }

        return back()->with('success', 'Positions des boxes mises à jour avec succès.');
    }

    /**
     * Update floor plan settings
     */
    public function updateFloorSettings(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'plan_width' => 'nullable|integer|min:400|max:5000',
            'plan_height' => 'nullable|integer|min:300|max:5000',
            'plan_background_image' => 'nullable|string',
            'plan_settings' => 'nullable|array',
        ]);

        $floor->update($validated);

        return back()->with('success', 'Paramètres du plan mis à jour avec succès.');
    }

    /**
     * Auto-organize boxes on floor plan (grid layout)
     */
    public function autoOrganize(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'layout' => 'required|in:grid,rows,columns',
            'spacing' => 'nullable|integer|min:10|max:100',
            'box_width' => 'nullable|integer|min:50|max:200',
            'box_height' => 'nullable|integer|min:50|max:200',
        ]);

        $boxes = $floor->boxes()->orderBy('number')->get();
        $spacing = $validated['spacing'] ?? 20;
        $boxWidth = $validated['box_width'] ?? 100;
        $boxHeight = $validated['box_height'] ?? 100;
        $layout = $validated['layout'];

        $startX = 50;
        $startY = 50;

        switch ($layout) {
            case 'grid':
                // Calculate optimal columns
                $cols = (int) ceil(sqrt($boxes->count()));
                $x = $startX;
                $y = $startY;
                $col = 0;

                foreach ($boxes as $box) {
                    $box->update([
                        'plan_x' => $x,
                        'plan_y' => $y,
                        'plan_width' => $boxWidth,
                        'plan_height' => $boxHeight,
                    ]);

                    $col++;
                    if ($col >= $cols) {
                        $col = 0;
                        $x = $startX;
                        $y += $boxHeight + $spacing;
                    } else {
                        $x += $boxWidth + $spacing;
                    }
                }
                break;

            case 'rows':
                $y = $startY;
                foreach ($boxes as $box) {
                    $box->update([
                        'plan_x' => $startX,
                        'plan_y' => $y,
                        'plan_width' => $boxWidth,
                        'plan_height' => $boxHeight,
                    ]);
                    $y += $boxHeight + $spacing;
                }
                break;

            case 'columns':
                $x = $startX;
                foreach ($boxes as $box) {
                    $box->update([
                        'plan_x' => $x,
                        'plan_y' => $startY,
                        'plan_width' => $boxWidth,
                        'plan_height' => $boxHeight,
                    ]);
                    $x += $boxWidth + $spacing;
                }
                break;
        }

        return back()->with('success', 'Boxes organisées automatiquement en mode ' . $layout . '.');
    }

    /**
     * Get floor plan data (AJAX)
     */
    public function getFloorData(Floor $floor)
    {
        $floor->load([
            'boxes' => function ($query) {
                $query->with('contract')->orderBy('number');
            }
        ]);

        return response()->json([
            'floor' => $floor,
            'boxes' => $floor->boxes->map(function ($box) {
                return [
                    'id' => $box->id,
                    'number' => $box->number,
                    'size' => $box->size,
                    'type' => $box->type,
                    'status' => $box->status,
                    'current_price' => $box->current_price,
                    'plan_x' => $box->plan_x ?? 0,
                    'plan_y' => $box->plan_y ?? 0,
                    'plan_width' => $box->plan_width ?? 100,
                    'plan_height' => $box->plan_height ?? 100,
                    'plan_color' => $box->plan_color,
                    'contract' => $box->contract,
                ];
            }),
        ]);
    }
}
