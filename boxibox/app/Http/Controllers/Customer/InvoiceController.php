<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display customer's invoices
     */
    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        $invoices = $customer->invoices()
            ->with(['contract.box'])
            ->orderByDesc('invoice_date')
            ->paginate(20);

        $stats = [
            'total_pending' => $customer->invoices()->where('status', 'pending')->sum('total_ttc'),
            'total_overdue' => $customer->invoices()->where('status', 'overdue')->sum('total_ttc'),
            'total_paid_this_year' => $customer->invoices()
                ->where('status', 'paid')
                ->whereYear('invoice_date', now()->year)
                ->sum('total_ttc'),
        ];

        return Inertia::render('Customer/Invoices/Index', [
            'invoices' => $invoices,
            'stats' => $stats,
        ]);
    }

    /**
     * Display invoice details
     */
    public function show(Request $request, Invoice $invoice)
    {
        // Ensure customer owns this invoice
        if ($invoice->contract->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $invoice->load(['contract.box', 'payments']);

        return Inertia::render('Customer/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Download invoice PDF
     */
    public function download(Request $request, Invoice $invoice)
    {
        // Ensure customer owns this invoice
        if ($invoice->contract->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        $invoice->load(['contract.customer', 'contract.box.floor.building.site']);

        $pdf = PDF::loadView('pdfs.invoice', ['invoice' => $invoice]);

        return $pdf->download("facture_{$invoice->invoice_number}.pdf");
    }
}
