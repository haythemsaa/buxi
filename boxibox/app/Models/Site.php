<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'address',
        'city',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'phone',
        'email',
        'opening_hours',
        'access_hours',
        'features',
        'photos',
        'is_active',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'access_hours' => 'array',
        'features' => 'array',
        'photos' => 'array',
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\Spatie\Multitenancy\Models\Tenant::class, 'tenant_id', 'id');
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function boxes(): HasMany
    {
        return $this->hasManyThrough(Box::class, Building::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors & Mutators
    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->postal_code} {$this->city}, {$this->country}";
    }

    // Helper methods
    public function getOccupancyRate(): float
    {
        $totalBoxes = $this->boxes()->count();
        if ($totalBoxes === 0) {
            return 0;
        }

        $occupiedBoxes = $this->boxes()->where('status', 'occupied')->count();
        return round(($occupiedBoxes / $totalBoxes) * 100, 2);
    }
}
