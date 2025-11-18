<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Multitenancy\Models\Tenant;

class InvoiceController extends Controller
{
    /**
     * Get customer's invoices
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $invoices = $customer->contracts()
            ->with(['invoices.payments'])
            ->get()
            ->pluck('invoices')
            ->flatten()
            ->sortByDesc('invoice_date')
            ->values()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date,
                    'due_date' => $invoice->due_date,
                    'total_ht' => $invoice->total_ht,
                    'tax_amount' => $invoice->tax_amount,
                    'total_ttc' => $invoice->total_ttc,
                    'paid_amount' => $invoice->paid_amount,
                    'remaining_amount' => $invoice->total_ttc - ($invoice->paid_amount ?? 0),
                    'status' => $invoice->status,
                    'status_label' => $this->getStatusLabel($invoice->status),
                    'contract_number' => $invoice->contract->contract_number,
                ];
            });

        return response()->json([
            'invoices' => $invoices,
        ]);
    }

    /**
     * Get invoice details
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $customer = $request->user();

        $invoice = $customer->contracts()
            ->with(['invoices.payments', 'invoices.contract.box'])
            ->get()
            ->pluck('invoices')
            ->flatten()
            ->firstWhere('id', $id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Facture non trouvée',
            ], 404);
        }

        return response()->json([
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'paid_at' => $invoice->paid_at,
                'total_ht' => $invoice->total_ht,
                'tax_rate' => $invoice->tax_rate,
                'tax_amount' => $invoice->tax_amount,
                'total_ttc' => $invoice->total_ttc,
                'paid_amount' => $invoice->paid_amount,
                'remaining_amount' => $invoice->total_ttc - ($invoice->paid_amount ?? 0),
                'status' => $invoice->status,
                'status_label' => $this->getStatusLabel($invoice->status),
                'notes' => $invoice->notes,
                'line_items' => $invoice->line_items,
                'contract' => [
                    'contract_number' => $invoice->contract->contract_number,
                    'box_number' => $invoice->contract->box->number,
                ],
                'payments' => $invoice->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_number' => $payment->payment_number,
                        'amount' => $payment->amount,
                        'payment_date' => $payment->payment_date,
                        'method' => $payment->method,
                        'method_label' => $this->getPaymentMethodLabel($payment->method),
                        'status' => $payment->status,
                        'status_label' => $this->getPaymentStatusLabel($payment->status),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Download invoice PDF
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request, $id)
    {
        $customer = $request->user();

        // Récupérer la facture du client
        $invoice = Invoice::whereHas('contract', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })
        ->with([
            'contract.customer',
            'contract.box.floor.building.site',
            'payments'
        ])
        ->find($id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Facture non trouvée',
            ], 404);
        }

        // Récupérer les informations du tenant
        $tenant = Tenant::current();

        // Générer le PDF
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'tenant' => $tenant,
        ]);

        // Retourner le PDF en tant que téléchargement
        return $pdf->download('facture-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Get invoice status label
     *
     * @param string $status
     * @return string
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'paid' => 'Payée',
            'overdue' => 'En retard',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get payment method label
     *
     * @param string $method
     * @return string
     */
    private function getPaymentMethodLabel($method)
    {
        $labels = [
            'sepa' => 'Prélèvement SEPA',
            'card' => 'Carte bancaire',
            'transfer' => 'Virement bancaire',
            'cash' => 'Espèces',
            'check' => 'Chèque',
        ];

        return $labels[$method] ?? $method;
    }

    /**
     * Get payment status label
     *
     * @param string $status
     * @return string
     */
    private function getPaymentStatusLabel($status)
    {
        $labels = [
            'succeeded' => 'Réussi',
            'pending' => 'En attente',
            'failed' => 'Échoué',
        ];

        return $labels[$status] ?? $status;
    }
}
