<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'name',
        'type',
        'construction_year',
        'total_surface',
        'climate_controlled',
        'description',
        'features',
        'display_order',
    ];

    protected $casts = [
        'features' => 'array',
        'climate_controlled' => 'boolean',
        'total_surface' => 'decimal:2',
    ];

    // Relations
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class)->orderBy('level');
    }

    public function boxes(): HasMany
    {
        return $this->hasManyThrough(Box::class, Floor::class);
    }

    // Scopes
    public function scopeInterior($query)
    {
        return $query->where('type', 'interior');
    }

    public function scopeExterior($query)
    {
        return $query->where('type', 'exterior');
    }

    public function scopeClimateControlled($query)
    {
        return $query->where('climate_controlled', true);
    }

    // Helper methods
    public function getTotalBoxesCount(): int
    {
        return $this->boxes()->count();
    }

    public function getOccupiedBoxesCount(): int
    {
        return $this->boxes()->where('status', 'occupied')->count();
    }

    public function getOccupancyRate(): float
    {
        $total = $this->getTotalBoxesCount();
        if ($total === 0) {
            return 0;
        }
        return round(($this->getOccupiedBoxesCount() / $total) * 100, 2);
    }
}
