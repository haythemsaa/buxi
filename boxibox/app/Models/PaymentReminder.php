<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentReminder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'customer_id',
        'contract_id',
        'phase',
        'days_overdue',
        'amount_due',
        'late_fee',
        'status',
        'sent_at',
        'acknowledged_at',
        'paid_at',
        'sent_via',
        'message',
        'metadata',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'days_overdue' => 'integer',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'paid_at' => 'datetime',
        'sent_via' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Configuration des phases de rappel
     */
    const PHASES_CONFIG = [
        'phase_1' => [
            'days' => 7,
            'name' => 'Premier rappel amical',
            'late_fee_percentage' => 0, // Pas de pénalité
            'severity' => 'low',
        ],
        'phase_2' => [
            'days' => 15,
            'name' => 'Rappel ferme',
            'late_fee_percentage' => 5, // 5% de pénalité
            'severity' => 'medium',
        ],
        'phase_3' => [
            'days' => 30,
            'name' => 'Mise en demeure',
            'late_fee_percentage' => 10, // 10% de pénalité
            'severity' => 'high',
        ],
    ];

    /**
     * Relations
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePhase($query, string $phase)
    {
        return $query->where('phase', $phase);
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['pending', 'sent', 'acknowledged']);
    }

    /**
     * Marquer comme envoyé
     */
    public function markAsSent(array $channels = ['email']): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_via' => $channels,
        ]);
    }

    /**
     * Marquer comme lu/accusé réception
     */
    public function markAsAcknowledged(): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Marquer comme payé
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Annuler le rappel
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Obtenir la configuration de la phase
     */
    public function getPhaseConfig(): array
    {
        return self::PHASES_CONFIG[$this->phase] ?? [];
    }

    /**
     * Obtenir le nom de la phase
     */
    public function getPhaseName(): string
    {
        return $this->getPhaseConfig()['name'] ?? $this->phase;
    }

    /**
     * Obtenir le niveau de sévérité
     */
    public function getSeverity(): string
    {
        return $this->getPhaseConfig()['severity'] ?? 'low';
    }

    /**
     * Calculer les pénalités de retard
     */
    public function calculateLateFee(): float
    {
        $percentage = $this->getPhaseConfig()['late_fee_percentage'] ?? 0;
        return round($this->amount_due * ($percentage / 100), 2);
    }

    /**
     * Montant total avec pénalités
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->amount_due + $this->late_fee;
    }

    /**
     * Vérifier si le rappel peut être envoyé
     */
    public function canBeSent(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Déterminer la phase suivante nécessaire
     */
    public static function getNextPhase(?string $currentPhase): ?string
    {
        if ($currentPhase === null) {
            return 'phase_1';
        }

        $phases = ['phase_1', 'phase_2', 'phase_3'];
        $currentIndex = array_search($currentPhase, $phases);

        if ($currentIndex === false || $currentIndex === count($phases) - 1) {
            return null; // Pas de phase suivante
        }

        return $phases[$currentIndex + 1];
    }

    /**
     * Vérifier si c'est le dernier rappel
     */
    public function isLastPhase(): bool
    {
        return $this->phase === 'phase_3';
    }

    /**
     * Obtenir la couleur d'affichage selon la phase
     */
    public function getColorAttribute(): string
    {
        return match($this->phase) {
            'phase_1' => 'yellow',
            'phase_2' => 'orange',
            'phase_3' => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône selon la phase
     */
    public function getIconAttribute(): string
    {
        return match($this->phase) {
            'phase_1' => '⚠️',
            'phase_2' => '🚨',
            'phase_3' => '🔴',
            default => 'ℹ️',
        };
    }
}
