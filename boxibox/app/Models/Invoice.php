<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'contract_id',
        'customer_id',
        'site_id',
        'type',
        'issue_date',
        'due_date',
        'period_start',
        'period_end',
        'subtotal_ht',
        'tax_amount',
        'tax_rate',
        'total_ttc',
        'discount_amount',
        'discount_reason',
        'line_items',
        'status',
        'amount_paid',
        'paid_at',
        'pdf_path',
        'xml_path',
        'reminder_count',
        'last_reminder_sent',
        'notes',
        'internal_notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'date',
        'last_reminder_sent' => 'date',
        'subtotal_ht' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'line_items' => 'array',
    ];

    // Relations
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentReminders(): HasMany
    {
        return $this->hasMany(PaymentReminder::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                  ->where('due_date', '<', now());
            });
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays($days)]);
    }

    // Helper methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' ||
               ($this->status === 'pending' && $this->due_date < now());
    }

    public function getRemainingAmount(): float
    {
        return $this->total_ttc - $this->amount_paid;
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'amount_paid' => $this->total_ttc,
            'paid_at' => now(),
        ]);
    }

    public function addPayment(float $amount): void
    {
        $newAmountPaid = $this->amount_paid + $amount;

        if ($newAmountPaid >= $this->total_ttc) {
            $this->update([
                'status' => 'paid',
                'amount_paid' => $this->total_ttc,
                'paid_at' => now(),
            ]);
        } else {
            $this->update([
                'status' => 'partial',
                'amount_paid' => $newAmountPaid,
            ]);
        }
    }
}
