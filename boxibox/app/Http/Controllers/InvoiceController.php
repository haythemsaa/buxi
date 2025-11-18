<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = 1; // TODO: Get from authenticated user's tenant

        $query = Invoice::whereHas('contract.customer', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->with(['contract.customer', 'contract.box', 'payments']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('contract', function ($q) use ($search) {
                        $q->where('contract_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('contract.customer', function ($q) use ($search) {
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

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(15);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load([
            'contract.customer',
            'contract.box',
            'payments'
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }
}
