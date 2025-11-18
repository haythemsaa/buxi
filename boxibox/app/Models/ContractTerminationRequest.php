<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractTerminationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'customer_id',
        'requested_termination_date',
        'reason',
        'status',
        'admin_notes',
        'approved_termination_date',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'requested_termination_date' => 'date',
        'approved_termination_date' => 'date',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the contract for this termination request
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get the customer who requested the termination
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who processed the request
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'En attente',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
