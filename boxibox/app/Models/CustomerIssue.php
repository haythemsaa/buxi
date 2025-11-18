<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'contract_id',
        'issue_number',
        'type',
        'subject',
        'description',
        'priority',
        'status',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($issue) {
            // Generate unique issue number
            $issue->issue_number = 'ISS-' . strtoupper(uniqid());
        });
    }

    /**
     * Get the customer that reported the issue
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the contract associated with the issue
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get the user who resolved the issue
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'open' => 'Ouvert',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get the type label
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'access' => 'Accès',
            'maintenance' => 'Maintenance',
            'billing' => 'Facturation',
            'security' => 'Sécurité',
            'other' => 'Autre',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Get the priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        $labels = [
            'low' => 'Basse',
            'medium' => 'Moyenne',
            'high' => 'Haute',
            'urgent' => 'Urgente',
        ];

        return $labels[$this->priority] ?? $this->priority;
    }
}
