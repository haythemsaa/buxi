<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->issue_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'total_ht' => (float) $this->subtotal_ht,
            'tax_amount' => (float) $this->tax_amount,
            'total_ttc' => (float) $this->total_ttc,
            'paid_amount' => (float) $this->amount_paid,
            'remaining_amount' => (float) $this->getRemainingAmount(),
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'contract_number' => $this->contract->contract_number ?? null,
            'paid_at' => $this->paid_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
