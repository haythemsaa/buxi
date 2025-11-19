<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display customer dashboard
     */
    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            return redirect()->route('dashboard');
        }

        $activeContracts = $customer->contracts()
            ->where('status', 'active')
            ->with(['box.floor.building.site'])
            ->get();

        $pendingInvoices = $customer->invoices()
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $recentPayments = $customer->payments()
            ->with('invoice')
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get();

        $loyaltyPoints = $customer->loyalty_points ?? 0;

        $stats = [
            'total_contracts' => $activeContracts->count(),
            'total_due' => $pendingInvoices->sum('total_ttc'),
            'next_payment_date' => $pendingInvoices->first()?->due_date,
        ];

        return Inertia::render('Customer/Dashboard', [
            'active_contracts' => $activeContracts,
            'pending_invoices' => $pendingInvoices,
            'recent_payments' => $recentPayments,
            'loyalty_points' => $loyaltyPoints,
            'stats' => $stats,
        ]);
    }
}
