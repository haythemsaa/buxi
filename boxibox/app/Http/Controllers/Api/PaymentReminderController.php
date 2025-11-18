<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentReminder;
use Illuminate\Http\Request;

class PaymentReminderController extends Controller
{
    /**
     * Liste des rappels de paiement du client
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $reminders = PaymentReminder::where('customer_id', $customer->id)
            ->with(['invoice'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($reminder) {
                return [
                    'id' => $reminder->id,
                    'phase' => $reminder->phase,
                    'phase_name' => $reminder->getPhaseName(),
                    'invoice_number' => $reminder->invoice->invoice_number,
                    'amount_due' => $reminder->amount_due,
                    'late_fee' => $reminder->late_fee,
                    'total_amount' => $reminder->total_amount,
                    'days_overdue' => $reminder->days_overdue,
                    'status' => $reminder->status,
                    'sent_at' => $reminder->sent_at,
                    'message' => $reminder->message,
                    'severity' => $reminder->getSeverity(),
                    'color' => $reminder->color,
                    'icon' => $reminder->icon,
                    'created_at' => $reminder->created_at,
                ];
            });

        return response()->json(['reminders' => $reminders]);
    }

    /**
     * Détails d'un rappel spécifique
     */
    public function show(Request $request, $id)
    {
        $customer = $request->user();

        $reminder = PaymentReminder::where('customer_id', $customer->id)
            ->with(['invoice', 'contract'])
            ->findOrFail($id);

        return response()->json([
            'reminder' => [
                'id' => $reminder->id,
                'phase' => $reminder->phase,
                'phase_name' => $reminder->getPhaseName(),
                'days_overdue' => $reminder->days_overdue,
                'amount_due' => $reminder->amount_due,
                'late_fee' => $reminder->late_fee,
                'total_amount' => $reminder->total_amount,
                'status' => $reminder->status,
                'sent_at' => $reminder->sent_at,
                'acknowledged_at' => $reminder->acknowledged_at,
                'message' => $reminder->message,
                'severity' => $reminder->getSeverity(),
                'is_last_phase' => $reminder->isLastPhase(),
                'invoice' => [
                    'number' => $reminder->invoice->invoice_number,
                    'issue_date' => $reminder->invoice->issue_date,
                    'due_date' => $reminder->invoice->due_date,
                    'total' => $reminder->invoice->total_ttc,
                    'amount_paid' => $reminder->invoice->amount_paid,
                    'remaining' => $reminder->invoice->getRemainingAmount(),
                ],
                'contract' => $reminder->contract ? [
                    'number' => $reminder->contract->contract_number,
                ] : null,
                'created_at' => $reminder->created_at,
            ],
        ]);
    }

    /**
     * Marquer un rappel comme accusé réception
     */
    public function acknowledge(Request $request, $id)
    {
        $customer = $request->user();

        $reminder = PaymentReminder::where('customer_id', $customer->id)
            ->where('status', 'sent')
            ->findOrFail($id);

        $reminder->markAsAcknowledged();

        return response()->json([
            'message' => 'Rappel accusé réception',
            'reminder' => [
                'id' => $reminder->id,
                'status' => $reminder->status,
                'acknowledged_at' => $reminder->acknowledged_at,
            ],
        ]);
    }
}
