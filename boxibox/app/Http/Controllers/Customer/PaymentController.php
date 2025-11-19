<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentGatewayService $paymentService
    ) {}

    /**
     * Display customer's payment history
     */
    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        $payments = $customer->payments()
            ->with('invoice')
            ->orderByDesc('payment_date')
            ->paginate(20);

        return Inertia::render('Customer/Payments/Index', [
            'payments' => $payments,
        ]);
    }

    /**
     * Process payment for an invoice
     */
    public function pay(Request $request, Invoice $invoice)
    {
        // Ensure customer owns this invoice
        if ($invoice->contract->customer_id !== $request->user()->customer->id) {
            abort(403);
        }

        // Check if already paid
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cette facture est déjà payée.');
        }

        $validated = $request->validate([
            'gateway' => 'required|in:stripe,paypal,sepa',
            'payment_method_id' => 'required_if:gateway,stripe',
            'iban' => 'required_if:gateway,sepa',
        ]);

        try {
            $payment = $this->paymentService->charge(
                $invoice,
                $validated['gateway'],
                $validated
            );

            return redirect()->route('customer.payments.index')
                ->with('success', 'Paiement effectué avec succès !');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du paiement: ' . $e->getMessage());
        }
    }

    /**
     * PayPal payment success callback
     */
    public function paypalSuccess(Request $request)
    {
        // Handle PayPal success
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');

        // Execute payment with PayPal
        // TODO: Complete PayPal execution

        return redirect()->route('customer.payments.index')
            ->with('success', 'Paiement PayPal effectué avec succès !');
    }

    /**
     * PayPal payment cancel callback
     */
    public function paypalCancel(Request $request)
    {
        return redirect()->route('customer.invoices.index')
            ->with('error', 'Paiement PayPal annulé.');
    }
}
