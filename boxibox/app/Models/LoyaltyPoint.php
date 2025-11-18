<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'points',
        'points_earned',
        'points_spent',
        'tier',
    ];

    protected $casts = [
        'points' => 'integer',
        'points_earned' => 'integer',
        'points_spent' => 'integer',
    ];

    /**
     * Tiers configuration
     */
    const TIERS = [
        'bronze' => ['min' => 0, 'max' => 999, 'discount' => 0],
        'silver' => ['min' => 1000, 'max' => 4999, 'discount' => 5],
        'gold' => ['min' => 5000, 'max' => 9999, 'discount' => 10],
        'platinum' => ['min' => 10000, 'max' => PHP_INT_MAX, 'discount' => 15],
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($loyalty) {
            // Recalculer le tier automatiquement
            $loyalty->tier = self::calculateTier($loyalty->points);
        });
    }

    /**
     * Relations
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'customer_id', 'customer_id');
    }

    /**
     * Ajouter des points
     */
    public function addPoints(int $points, string $description, $related = null): LoyaltyTransaction
    {
        $this->increment('points', $points);
        $this->increment('points_earned', $points);

        return LoyaltyTransaction::create([
            'customer_id' => $this->customer_id,
            'type' => 'earned',
            'points' => $points,
            'description' => $description,
            'related_type' => $related ? get_class($related) : null,
            'related_id' => $related?->id,
            'expires_at' => now()->addYear(),
        ]);
    }

    /**
     * Dépenser des points
     */
    public function spendPoints(int $points, string $description, $related = null): ?LoyaltyTransaction
    {
        if ($this->points < $points) {
            return null; // Pas assez de points
        }

        $this->decrement('points', $points);
        $this->increment('points_spent', $points);

        return LoyaltyTransaction::create([
            'customer_id' => $this->customer_id,
            'type' => 'spent',
            'points' => -$points,
            'description' => $description,
            'related_type' => $related ? get_class($related) : null,
            'related_id' => $related?->id,
        ]);
    }

    /**
     * Calculer le tier basé sur les points
     */
    public static function calculateTier(int $points): string
    {
        foreach (self::TIERS as $tier => $range) {
            if ($points >= $range['min'] && $points <= $range['max']) {
                return $tier;
            }
        }

        return 'bronze';
    }

    /**
     * Obtenir le discount du tier actuel
     */
    public function getTierDiscountAttribute(): int
    {
        return self::TIERS[$this->tier]['discount'] ?? 0;
    }

    /**
     * Obtenir le label du tier
     */
    public function getTierLabelAttribute(): string
    {
        $labels = [
            'bronze' => 'Bronze',
            'silver' => 'Argent',
            'gold' => 'Or',
            'platinum' => 'Platine',
        ];

        return $labels[$this->tier] ?? 'Bronze';
    }

    /**
     * Points jusqu'au prochain tier
     */
    public function getPointsToNextTierAttribute(): ?int
    {
        $currentTier = $this->tier;
        $tiers = array_keys(self::TIERS);
        $currentIndex = array_search($currentTier, $tiers);

        if ($currentIndex === false || $currentIndex === count($tiers) - 1) {
            return null; // Déjà au max
        }

        $nextTier = $tiers[$currentIndex + 1];
        return self::TIERS[$nextTier]['min'] - $this->points;
    }
}
