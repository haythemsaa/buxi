<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_id',
        'level',
        'name',
        'has_elevator',
        'has_freight_elevator',
        'corridor_width',
        'description',
        'display_order',
    ];

    protected $casts = [
        'has_elevator' => 'boolean',
        'has_freight_elevator' => 'boolean',
        'corridor_width' => 'decimal:2',
    ];

    // Relations
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function boxes(): HasMany
    {
        return $this->hasMany(Box::class)->orderBy('number');
    }

    // Scopes
    public function scopeGroundFloor($query)
    {
        return $query->where('level', 0);
    }

    public function scopeBasement($query)
    {
        return $query->where('level', '<', 0);
    }

    public function scopeUpperFloors($query)
    {
        return $query->where('level', '>', 0);
    }

    public function scopeWithElevator($query)
    {
        return $query->where('has_elevator', true);
    }

    // Accessors
    public function getLevelNameAttribute(): string
    {
        if ($this->level === 0) {
            return 'Rez-de-chaussée';
        } elseif ($this->level < 0) {
            return 'Sous-sol ' . abs($this->level);
        } else {
            return 'Étage ' . $this->level;
        }
    }

    // Helper methods
    public function getTotalBoxesCount(): int
    {
        return $this->boxes()->count();
    }

    public function getAvailableBoxesCount(): int
    {
        return $this->boxes()->where('status', 'available')->count();
    }
}
