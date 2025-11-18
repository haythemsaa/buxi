<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reservation_number',
        'customer_id',
        'box_id',
        'site_id',
        'guest_email',
        'guest_first_name',
        'guest_last_name',
        'guest_phone',
        'start_date',
        'end_date',
        'duration_months',
        'monthly_price_ht',
        'tax_rate',
        'deposit_amount',
        'first_payment',
        'promotion_id',
        'discount_amount',
        'status',
        'confirmed_at',
        'expires_at',
        'converted_to_contract_at',
        'contract_id',
        'requires_insurance',
        'insurance_monthly',
        'selected_options',
        'notes',
        'admin_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_price_ht' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'first_payment' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'insurance_monthly' => 'decimal:2',
        'requires_insurance' => 'boolean',
        'confirmed_at' => 'datetime',
        'expires_at' => 'datetime',
        'converted_to_contract_at' => 'datetime',
        'selected_options' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (!$reservation->reservation_number) {
                $reservation->reservation_number = 'RES-' . strtoupper(uniqid());
            }

            // Expiration 30 jours par défaut
            if (!$reservation->expires_at) {
                $reservation->expires_at = now()->addDays(30);
            }
        });
    }

    /**
     * Relations
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function box(): BelongsTo
    {
        return $this->belongsTo(Box::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Methods
     */
    public function confirm()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    public function convertToContract(Contract $contract)
    {
        $this->update([
            'status' => 'converted',
            'contract_id' => $contract->id,
            'converted_to_contract_at' => now(),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getTotalPriceAttribute(): float
    {
        return $this->monthly_price_ht * (1 + $this->tax_rate / 100);
    }

    public function getCustomerNameAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        return $this->guest_first_name . ' ' . $this->guest_last_name;
    }

    public function getCustomerEmailAttribute(): string
    {
        return $this->customer?->email ?? $this->guest_email;
    }
}
