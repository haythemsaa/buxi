<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_number' => $this->contract_number,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'total_monthly_amount' => (float) $this->total_monthly_amount,
            'payment_method' => $this->payment_method,
            'payment_day' => $this->payment_day,
            'access_code' => $this->access_code,
            'box' => [
                'id' => $this->box->id,
                'number' => $this->box->number,
                'volume' => (float) $this->box->volume,
                'surface' => (float) $this->box->surface,
                'floor' => $this->box->floor->name ?? null,
                'building' => $this->box->floor->building->name ?? null,
                'site' => $this->box->site->name ?? null,
                'site_address' => $this->box->site->address ?? null,
                'site_city' => $this->box->site->city ?? null,
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
