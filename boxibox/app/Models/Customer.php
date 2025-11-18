<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasFactory, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'customer_number',
        'type',
        'civility',
        'first_name',
        'last_name',
        'birth_date',
        'company_name',
        'siret',
        'vat_number',
        'email',
        'password',
        'phone',
        'phone_secondary',
        'mobile',
        'address',
        'city',
        'postal_code',
        'country',
        'billing_address',
        'billing_city',
        'billing_postal_code',
        'billing_country',
        'language',
        'communication_preferences',
        'id_document_path',
        'proof_of_address_path',
        'bank_details_path',
        'kbis_path',
        'documents_verified',
        'payment_score',
        'tags',
        'acquisition_source',
        'is_vip',
        'internal_notes',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'communication_preferences' => 'array',
        'tags' => 'array',
        'is_vip' => 'boolean',
        'birth_date' => 'date',
        'password' => 'hashed',
    ];

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\Spatie\Multitenancy\Models\Tenant::class, 'tenant_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function activeContracts(): HasMany
    {
        return $this->hasMany(Contract::class)->where('status', 'active');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    // Scopes
    public function scopeIndividual($query)
    {
        return $query->where('type', 'individual');
    }

    public function scopeProfessional($query)
    {
        return $query->where('type', 'professional');
    }

    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        if ($this->type === 'professional' && $this->company_name) {
            return $this->company_name;
        }
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->postal_code} {$this->city}, {$this->country}";
    }

    // Helper methods
    public function hasActiveContracts(): bool
    {
        return $this->activeContracts()->exists();
    }

    public function getTotalOutstandingAmount(): float
    {
        return $this->invoices()
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->sum('total_ttc');
    }
}
