<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'customer_id',
        'box_id',
        'site_id',
        'start_date',
        'end_date',
        'actual_end_date',
        'initial_duration_months',
        'auto_renewal',
        'notice_period_days',
        'monthly_price',
        'deposit_amount',
        'setup_fee',
        'billing_frequency',
        'billing_day',
        'payment_method',
        'stripe_customer_id',
        'stripe_payment_method_id',
        'sepa_mandate_id',
        'has_insurance',
        'insurance_amount_monthly',
        'declared_value',
        'inventory',
        'forbidden_items_check',
        'contract_document_path',
        'terms_document_path',
        'inventory_document_path',
        'signed_at',
        'signature_ip',
        'status',
        'status_reason',
        'access_code',
        'badge_number',
        'access_hours',
        'termination_notice_date',
        'termination_reason',
        'termination_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_end_date' => 'date',
        'termination_notice_date' => 'date',
        'auto_renewal' => 'boolean',
        'has_insurance' => 'boolean',
        'signed_at' => 'datetime',
        'monthly_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'insurance_amount_monthly' => 'decimal:2',
        'declared_value' => 'decimal:2',
        'inventory' => 'array',
        'forbidden_items_check' => 'array',
        'access_hours' => 'array',
    ];

    // Relations
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeTerminated($query)
    {
        return $query->where('status', 'terminated');
    }

    public function scopeExpiringWithin($query, int $days)
    {
        return $query->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>=', now())
            ->where('status', 'active');
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function getTotalMonthlyAmount(): float
    {
        $total = $this->monthly_price;
        if ($this->has_insurance) {
            $total += $this->insurance_amount_monthly;
        }
        return $total;
    }

    public function getDurationInMonths(): int
    {
        $endDate = $this->actual_end_date ?? $this->end_date ?? now();
        return $this->start_date->diffInMonths($endDate);
    }
}
