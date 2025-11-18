<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Contract;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = 1; // TODO: Get from authenticated user's tenant

        $query = Contract::whereHas('customer', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->with(['customer', 'box']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('customer_number', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        $contracts = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Contracts/Index', [
            'contracts' => $contracts,
            'filters' => $request->only(['search', 'status', 'payment_method']),
        ]);
    }

    public function create(Request $request): Response
    {
        $tenantId = 1; // TODO: Get from authenticated user's tenant

        $customers = Customer::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('customer_number')
            ->get();

        $availableBoxes = Box::where('status', 'available')
            ->whereHas('floor.building.site', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->with('floor.building.site')
            ->get();

        return Inertia::render('Contracts/Create', [
            'customers' => $customers,
            'availableBoxes' => $availableBoxes,
            'selectedBoxId' => $request->input('box_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'box_id' => 'required|exists:boxes,id',
            'start_date' => 'required|date',
            'initial_duration_months' => 'nullable|integer|min:1',
            'price_monthly_ht' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'deposit_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:sepa,card,transfer,cash,check',
            'payment_day' => 'nullable|integer|min:1|max:28',
            'insurance_monthly' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check if box is available
        $box = Box::findOrFail($validated['box_id']);
        if ($box->status !== 'available') {
            return back()->withErrors(['box_id' => 'Ce box n\'est plus disponible.']);
        }

        // Generate contract number
        $latestContract = Contract::orderBy('id', 'desc')->first();
        $nextNumber = $latestContract ? (intval(substr($latestContract->contract_number, 2)) + 1) : 1;
        $validated['contract_number'] = 'CO' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        // Calculate amounts
        $validated['total_monthly_amount'] = $validated['price_monthly_ht'] + ($validated['insurance_monthly'] ?? 0);
        $validated['total_monthly_amount'] *= (1 + ($validated['tax_rate'] / 100));
        $validated['status'] = 'active';

        // Generate access code
        $validated['access_code'] = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $contract = Contract::create($validated);

        // Update box status
        $box->update(['status' => 'occupied']);

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Contrat créé avec succès.');
    }

    public function show(Contract $contract): Response
    {
        $contract->load([
            'customer',
            'box.floor.building.site',
            'invoices' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
            'invoices.payments'
        ]);

        return Inertia::render('Contracts/Show', [
            'contract' => $contract,
        ]);
    }

    public function edit(Contract $contract): Response
    {
        $tenantId = 1; // TODO: Get from authenticated user's tenant

        $customers = Customer::where('tenant_id', $tenantId)
            ->orderBy('customer_number')
            ->get();

        return Inertia::render('Contracts/Edit', [
            'contract' => $contract,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'price_monthly_ht' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'deposit_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:sepa,card,transfer,cash,check',
            'payment_day' => 'nullable|integer|min:1|max:28',
            'insurance_monthly' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Recalculate total monthly amount
        $validated['total_monthly_amount'] = $validated['price_monthly_ht'] + ($validated['insurance_monthly'] ?? 0);
        $validated['total_monthly_amount'] *= (1 + ($validated['tax_rate'] / 100));

        $contract->update($validated);

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Contrat modifié avec succès.');
    }

    public function destroy(Contract $contract)
    {
        if ($contract->status === 'active') {
            return back()->withErrors(['error' => 'Impossible de supprimer un contrat actif. Veuillez d\'abord le résilier.']);
        }

        $contract->delete();

        return redirect()->route('contracts.index')
            ->with('success', 'Contrat supprimé avec succès.');
    }

    public function suspend(Contract $contract)
    {
        if ($contract->status !== 'active') {
            return back()->withErrors(['error' => 'Seuls les contrats actifs peuvent être suspendus.']);
        }

        $contract->update([
            'status' => 'suspended',
            'suspension_date' => now(),
        ]);

        return back()->with('success', 'Contrat suspendu avec succès.');
    }

    public function reactivate(Contract $contract)
    {
        if ($contract->status !== 'suspended') {
            return back()->withErrors(['error' => 'Seuls les contrats suspendus peuvent être réactivés.']);
        }

        $contract->update([
            'status' => 'active',
            'reactivation_date' => now(),
        ]);

        return back()->with('success', 'Contrat réactivé avec succès.');
    }

    public function terminate(Contract $contract)
    {
        if ($contract->status === 'terminated') {
            return back()->withErrors(['error' => 'Ce contrat est déjà résilié.']);
        }

        $contract->update([
            'status' => 'terminated',
            'end_date' => now(),
        ]);

        // Update box status to available
        if ($contract->box) {
            $contract->box->update(['status' => 'available']);
        }

        return back()->with('success', 'Contrat résilié avec succès.');
    }
}
