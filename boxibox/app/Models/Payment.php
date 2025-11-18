<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'contract_id',
        'customer_id',
        'amount',
        'currency',
        'method',
        'payment_date',
        'reference',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'stripe_refund_id',
        'status',
        'status_message',
        'is_refund',
        'refunded_payment_id',
        'refunded_at',
        'refund_reason',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'refunded_at' => 'date',
        'amount' => 'decimal:2',
        'is_refund' => 'boolean',
    ];

    // Relations
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function refundedPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'refunded_payment_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Payment::class, 'refunded_payment_id');
    }

    // Scopes
    public function scopeSucceeded($query)
    {
        return $query->where('status', 'succeeded');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('method', $method);
    }

    // Helper methods
    public function isSucceeded(): bool
    {
        return $this->status === 'succeeded';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function canBeRefunded(): bool
    {
        return $this->isSucceeded() && !$this->is_refund;
    }
}
