<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'points',
        'description',
        'related_type',
        'related_id',
        'expires_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'expires_at' => 'date',
    ];

    /**
     * Relations
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeEarned($query)
    {
        return $query->where('type', 'earned');
    }

    public function scopeSpent($query)
    {
        return $query->where('type', 'spent');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Obtenir le label du type
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'earned' => 'Gagné',
            'spent' => 'Dépensé',
            'expired' => 'Expiré',
            'bonus' => 'Bonus',
            'referral' => 'Parrainage',
        ];

        return $labels[$this->type] ?? $this->type;
    }
}
