<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Box extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'floor_id',
        'number',
        'name',
        'type',
        'size_category',
        'length',
        'width',
        'height',
        'volume',
        'surface',
        'base_price_monthly',
        'current_price_monthly',
        'ground_floor',
        'vehicle_access',
        'climate_controlled',
        'has_electricity',
        'features',
        'photos',
        'status',
        'status_note',
        'available_from',
    ];

    protected $casts = [
        'features' => 'array',
        'photos' => 'array',
        'ground_floor' => 'boolean',
        'vehicle_access' => 'boolean',
        'climate_controlled' => 'boolean',
        'has_electricity' => 'boolean',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'volume' => 'decimal:3',
        'surface' => 'decimal:2',
        'base_price_monthly' => 'decimal:2',
        'current_price_monthly' => 'decimal:2',
        'available_from' => 'date',
    ];

    // Relations
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function building(): BelongsTo
    {
        return $this->floor->building();
    }

    public function site(): BelongsTo
    {
        return $this->floor->building->site();
    }

    public function currentContract(): HasOne
    {
        return $this->hasOne(Contract::class)->where('status', 'active')->latest();
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBySizeCategory($query, string $category)
    {
        return $query->where('size_category', $category);
    }

    // Helper methods
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    public function getDimensionsAttribute(): string
    {
        return "{$this->length}cm x {$this->width}cm x {$this->height}cm";
    }
}
