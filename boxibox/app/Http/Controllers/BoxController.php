<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoxController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = 1; // TODO: Get from authenticated user's tenant

        $query = Box::with([
            'floor.building.site',
            'currentContract'
        ])->whereHas('floor.building.site', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('number', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('site_id')) {
            $query->whereHas('floor.building.site', function ($q) use ($request) {
                $q->where('id', $request->input('site_id'));
            });
        }

        $boxes = $query->orderBy('number')->paginate(15);

        $sites = Site::where('tenant_id', $tenantId)->get();

        return Inertia::render('Boxes/Index', [
            'boxes' => $boxes,
            'sites' => $sites,
            'filters' => $request->only(['search', 'status', 'site_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Boxes/Create');
    }

    public function store(Request $request)
    {
        // TODO: Implement box creation
    }

    public function show(Box $box): Response
    {
        $box->load(['floor.building.site', 'currentContract.customer']);

        return Inertia::render('Boxes/Show', [
            'box' => $box,
        ]);
    }

    public function edit(Box $box): Response
    {
        return Inertia::render('Boxes/Edit', [
            'box' => $box,
        ]);
    }

    public function update(Request $request, Box $box)
    {
        // TODO: Implement box update
    }

    public function destroy(Box $box)
    {
        // TODO: Implement box deletion
    }
}
