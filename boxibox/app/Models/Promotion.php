<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'free_months',
        'min_duration_months',
        'min_amount',
        'applicable_box_types',
        'applicable_sites',
        'valid_from',
        'valid_until',
        'is_active',
        'max_uses',
        'current_uses',
        'max_uses_per_customer',
        'online_only',
        'new_customers_only',
        'is_public',
        'requires_code',
        'auto_apply',
        'priority',
        'stackable',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_duration_months' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'applicable_box_types' => 'array',
        'applicable_sites' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
        'online_only' => 'boolean',
        'new_customers_only' => 'boolean',
        'is_public' => 'boolean',
        'requires_code' => 'boolean',
        'auto_apply' => 'boolean',
        'stackable' => 'boolean',
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

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Méthodes
     */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->valid_from->isFuture()) return false;
        if ($this->valid_until && $this->valid_until->isPast()) return false;
        if ($this->max_uses && $this->current_uses >= $this->max_uses) return false;

        return true;
    }

    public function canBeUsedBy(Customer $customer = null): bool
    {
        if (!$this->isValid()) return false;

        if ($this->new_customers_only && $customer) {
            // Vérifier si le client a déjà des contrats
            if ($customer->contracts()->count() > 0) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount(float $price, int $duration_months = 1): float
    {
        switch ($this->discount_type) {
            case 'percentage':
                return $price * ($this->discount_value / 100);

            case 'fixed_amount':
                return $this->discount_value;

            case 'first_month_free':
                return $price;

            case 'months_free':
                return $price * $this->free_months;

            default:
                return 0;
        }
    }

    public function incrementUses()
    {
        $this->increment('current_uses');
    }
}
