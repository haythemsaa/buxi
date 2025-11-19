<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
    /**
     * Display customer's contracts
     */
    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        $contracts = $customer->contracts()
            ->with(['box.floor.building.site'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Customer/Contracts/Index', [
            'contracts' => $contracts,
        ]);
    }

    /**
     * Display contract details
     */
    public function show(Request $request, Contract $contract)
    {
        // Ensure customer owns this contract
        if ($contract->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $contract->load(['box.floor.building.site', 'invoices', 'payments']);

        return Inertia::render('Customer/Contracts/Show', [
            'contract' => $contract,
        ]);
    }

    /**
     * Download contract PDF
     */
    public function download(Request $request, Contract $contract)
    {
        // Ensure customer owns this contract
        if ($contract->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $contract->load(['customer', 'box.floor.building.site']);

        $pdf = PDF::loadView('pdfs.contract', ['contract' => $contract]);

        return $pdf->download("contrat_{$contract->contract_number}.pdf");
    }

    /**
     * Request contract termination
     */
    public function requestTermination(Request $request, Contract $contract)
    {
        // Ensure customer owns this contract
        if ($contract->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'termination_date' => 'required|date|after:today',
            'reason' => 'nullable|string|max:500',
        ]);

        // Update contract with termination request
        $contract->update([
            'status' => 'termination_requested',
            'termination_date' => $validated['termination_date'],
            'termination_reason' => $validated['reason'] ?? null,
        ]);

        // TODO: Send notification to admin

        return back()->with('success', 'Demande de résiliation envoyée avec succès.');
    }
}
