<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentReminderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice->invoice_number ?? null,
            'phase' => $this->phase,
            'phase_name' => $this->getPhaseName(),
            'severity' => $this->getSeverity(),
            'days_overdue' => $this->days_overdue,
            'amount_due' => (float) $this->amount_due,
            'late_fee' => (float) $this->late_fee,
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'sent_at' => $this->sent_at?->toISOString(),
            'acknowledged_at' => $this->acknowledged_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'contract_number' => $this->contract->contract_number ?? null,
            'box_number' => $this->contract->box->number ?? null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
