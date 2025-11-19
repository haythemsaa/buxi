<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PricingRuleController extends Controller
{
    /**
     * Display a listing of pricing rules.
     */
    public function index()
    {
        $rules = PricingRule::with('site')
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Admin/PricingRules/Index', [
            'rules' => $rules,
        ]);
    }

    /**
     * Show the form for creating a new pricing rule.
     */
    public function create()
    {
        $sites = Site::all();

        return Inertia::render('Admin/PricingRules/Create', [
            'sites' => $sites,
        ]);
    }

    /**
     * Store a newly created pricing rule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'nullable|exists:sites,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'box_size_min' => 'nullable|numeric|min:0',
            'box_size_max' => 'nullable|numeric|min:0',
            'occupancy_threshold_min' => 'nullable|numeric|min:0|max:100',
            'occupancy_threshold_max' => 'nullable|numeric|min:0|max:100',
            'season' => 'required|in:winter,spring,summer,fall,all',
            'duration_months_min' => 'nullable|integer|min:1',
            'duration_months_max' => 'nullable|integer|min:1',
            'days_of_week' => 'nullable|array',
            'adjustment_type' => 'required|in:percentage,fixed',
            'adjustment_value' => 'required|numeric',
            'priority' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        $rule = PricingRule::create($validated);

        return redirect()->route('admin.pricing-rules.index')
            ->with('success', "Règle de tarification '{$rule->name}' créée avec succès.");
    }

    /**
     * Show the form for editing the specified pricing rule.
     */
    public function edit(PricingRule $pricingRule)
    {
        $sites = Site::all();

        return Inertia::render('Admin/PricingRules/Edit', [
            'rule' => $pricingRule->load('site'),
            'sites' => $sites,
        ]);
    }

    /**
     * Update the specified pricing rule.
     */
    public function update(Request $request, PricingRule $pricingRule)
    {
        $validated = $request->validate([
            'site_id' => 'nullable|exists:sites,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'box_size_min' => 'nullable|numeric|min:0',
            'box_size_max' => 'nullable|numeric|min:0',
            'occupancy_threshold_min' => 'nullable|numeric|min:0|max:100',
            'occupancy_threshold_max' => 'nullable|numeric|min:0|max:100',
            'season' => 'required|in:winter,spring,summer,fall,all',
            'duration_months_min' => 'nullable|integer|min:1',
            'duration_months_max' => 'nullable|integer|min:1',
            'days_of_week' => 'nullable|array',
            'adjustment_type' => 'required|in:percentage,fixed',
            'adjustment_value' => 'required|numeric',
            'priority' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        $pricingRule->update($validated);

        return redirect()->route('admin.pricing-rules.index')
            ->with('success', "Règle de tarification '{$pricingRule->name}' mise à jour.");
    }

    /**
     * Remove the specified pricing rule.
     */
    public function destroy(PricingRule $pricingRule)
    {
        $name = $pricingRule->name;
        $pricingRule->delete();

        return redirect()->route('admin.pricing-rules.index')
            ->with('success', "Règle de tarification '{$name}' supprimée.");
    }

    /**
     * Toggle active status
     */
    public function toggle(PricingRule $pricingRule)
    {
        $pricingRule->update([
            'is_active' => !$pricingRule->is_active,
        ]);

        $status = $pricingRule->is_active ? 'activée' : 'désactivée';

        return back()->with('success', "Règle '{$pricingRule->name}' {$status}.");
    }
}
