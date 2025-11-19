<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservation_number' => $this->reservation_number,
            'box_number' => $this->box->number ?? null,
            'site_name' => $this->site->name ?? null,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'duration_months' => $this->duration_months,
            'monthly_price_ht' => (float) $this->monthly_price_ht,
            'total_monthly_ttc' => (float) $this->total_monthly_ttc,
            'first_payment' => (float) $this->first_payment,
            'status' => $this->status,
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
