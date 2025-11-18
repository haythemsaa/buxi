<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'rule_type',
        'adjustment_value',
        'adjustment_type',
        'min_duration_months',
        'applicable_sites',
        'applicable_box_sizes',
        'min_occupancy_rate',
        'max_occupancy_rate',
        'valid_from',
        'valid_until',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'adjustment_value' => 'decimal:2',
        'min_occupancy_rate' => 'decimal:2',
        'max_occupancy_rate' => 'decimal:2',
        'applicable_sites' => 'array',
        'applicable_box_sizes' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            });
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Vérifier si la règle est applicable
     */
    public function isApplicable(Box $box, int $duration_months, float $occupancy_rate = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Vérifier durée
        if ($this->min_duration_months && $duration_months < $this->min_duration_months) {
            return false;
        }

        // Vérifier sites
        if ($this->applicable_sites && !in_array($box->floor->building->site_id, $this->applicable_sites)) {
            return false;
        }

        // Vérifier taille de box
        if ($this->applicable_box_sizes && !in_array($box->size_category, $this->applicable_box_sizes)) {
            return false;
        }

        // Vérifier taux d'occupation
        if ($occupancy_rate !== null) {
            if ($this->min_occupancy_rate && $occupancy_rate < $this->min_occupancy_rate) {
                return false;
            }
            if ($this->max_occupancy_rate && $occupancy_rate > $this->max_occupancy_rate) {
                return false;
            }
        }

        return true;
    }

    /**
     * Appliquer la règle
     */
    public function apply(float $price): float
    {
        if ($this->adjustment_type === 'percentage') {
            return $price * (1 - ($this->adjustment_value / 100));
        } else {
            return $price - $this->adjustment_value;
        }
    }

    /**
     * Vérifier si la règle est valide
     */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->valid_from->isFuture()) return false;
        if ($this->valid_until && $this->valid_until->isPast()) return false;

        return true;
    }

    /**
     * Obtenir le label du type
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'duration_discount' => 'Réduction durée',
            'seasonal' => 'Saisonnière',
            'occupancy_based' => 'Basée sur l\'occupation',
            'new_customer' => 'Nouveau client',
            'referral' => 'Parrainage',
        ];

        return $labels[$this->rule_type] ?? $this->rule_type;
    }
}
