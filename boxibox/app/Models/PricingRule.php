<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'description',
        'box_size_min',
        'box_size_max',
        'occupancy_threshold_min',
        'occupancy_threshold_max',
        'season',
        'duration_months_min',
        'duration_months_max',
        'days_of_week',
        'adjustment_type',
        'adjustment_value',
        'priority',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'box_size_min' => 'decimal:2',
        'box_size_max' => 'decimal:2',
        'occupancy_threshold_min' => 'decimal:2',
        'occupancy_threshold_max' => 'decimal:2',
        'adjustment_value' => 'decimal:2',
        'priority' => 'integer',
        'duration_months_min' => 'integer',
        'duration_months_max' => 'integer',
        'is_active' => 'boolean',
        'days_of_week' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    /**
     * Get the site that owns the pricing rule.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Scope for active rules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            });
    }

    /**
     * Scope for rules applicable to a specific box
     */
    public function scopeApplicableToBox($query, Box $box, float $occupancyRate, int $durationMonths = null)
    {
        $season = $this->getCurrentSeason();

        return $query->active()
            ->where(function ($q) use ($box) {
                $q->whereNull('site_id')
                  ->orWhere('site_id', $box->floor->building->site_id);
            })
            ->where(function ($q) use ($box) {
                $q->whereNull('box_size_min')
                  ->orWhere('box_size_min', '<=', $box->size_m3);
            })
            ->where(function ($q) use ($box) {
                $q->whereNull('box_size_max')
                  ->orWhere('box_size_max', '>=', $box->size_m3);
            })
            ->where(function ($q) use ($occupancyRate) {
                $q->whereNull('occupancy_threshold_min')
                  ->orWhere('occupancy_threshold_min', '<=', $occupancyRate);
            })
            ->where(function ($q) use ($occupancyRate) {
                $q->whereNull('occupancy_threshold_max')
                  ->orWhere('occupancy_threshold_max', '>=', $occupancyRate);
            })
            ->where(function ($q) use ($season) {
                $q->where('season', 'all')
                  ->orWhere('season', $season);
            })
            ->where(function ($q) use ($durationMonths) {
                if ($durationMonths) {
                    $q->whereNull('duration_months_min')
                      ->orWhere('duration_months_min', '<=', $durationMonths);
                }
            })
            ->where(function ($q) use ($durationMonths) {
                if ($durationMonths) {
                    $q->whereNull('duration_months_max')
                      ->orWhere('duration_months_max', '>=', $durationMonths);
                }
            })
            ->orderByDesc('priority');
    }

    /**
     * Get current season based on date
     */
    private function getCurrentSeason(): string
    {
        $month = now()->month;

        return match (true) {
            in_array($month, [12, 1, 2]) => 'winter',
            in_array($month, [3, 4, 5]) => 'spring',
            in_array($month, [6, 7, 8]) => 'summer',
            in_array($month, [9, 10, 11]) => 'fall',
        };
    }

    /**
     * Check if rule is currently valid
     */
    public function isCurrentlyValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Apply this rule to a base price
     */
    public function applyToPrice(float $basePrice): float
    {
        if ($this->adjustment_type === 'percentage') {
            return $basePrice * (1 + ($this->adjustment_value / 100));
        }

        return $basePrice + $this->adjustment_value;
    }
}
