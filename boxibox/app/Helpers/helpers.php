<?php

if (!function_exists('format_currency')) {
    /**
     * Format a number as currency (EUR)
     */
    function format_currency(float $amount, bool $showSymbol = true): string
    {
        $formatted = number_format($amount, 2, ',', ' ');
        return $showSymbol ? $formatted . ' €' : $formatted;
    }
}

if (!function_exists('format_phone')) {
    /**
     * Format a phone number
     */
    function format_phone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Format French phone numbers
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            return chunk_split($phone, 2, ' ');
        }

        return $phone;
    }
}

if (!function_exists('generate_customer_number')) {
    /**
     * Generate a unique customer number
     */
    function generate_customer_number(): string
    {
        do {
            $number = 'CL' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (\App\Models\Customer::where('customer_number', $number)->exists());

        return $number;
    }
}

if (!function_exists('generate_contract_number')) {
    /**
     * Generate a unique contract number
     */
    function generate_contract_number(): string
    {
        do {
            $number = 'CO' . date('Ymd') . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (\App\Models\Contract::where('contract_number', $number)->exists());

        return $number;
    }
}

if (!function_exists('generate_invoice_number')) {
    /**
     * Generate a unique invoice number
     */
    function generate_invoice_number(): string
    {
        do {
            $number = 'INV-' . date('Ymd') . '-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (\App\Models\Invoice::where('invoice_number', $number)->exists());

        return $number;
    }
}

if (!function_exists('generate_reservation_number')) {
    /**
     * Generate a unique reservation number
     */
    function generate_reservation_number(): string
    {
        do {
            $number = 'RES-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (\App\Models\Reservation::where('reservation_number', $number)->exists());

        return $number;
    }
}

if (!function_exists('calculate_ttc')) {
    /**
     * Calculate TTC (total including tax) from HT (excluding tax)
     */
    function calculate_ttc(float $ht, float $taxRate = 20.0): float
    {
        return round($ht * (1 + $taxRate / 100), 2);
    }
}

if (!function_exists('calculate_ht')) {
    /**
     * Calculate HT (excluding tax) from TTC (including tax)
     */
    function calculate_ht(float $ttc, float $taxRate = 20.0): float
    {
        return round($ttc / (1 + $taxRate / 100), 2);
    }
}

if (!function_exists('calculate_tax_amount')) {
    /**
     * Calculate tax amount from HT
     */
    function calculate_tax_amount(float $ht, float $taxRate = 20.0): float
    {
        return round($ht * ($taxRate / 100), 2);
    }
}

if (!function_exists('get_payment_method_label')) {
    /**
     * Get human-readable payment method label
     */
    function get_payment_method_label(string $method): string
    {
        return match ($method) {
            'cash' => 'Espèces',
            'check' => 'Chèque',
            'bank_transfer' => 'Virement bancaire',
            'sepa' => 'Prélèvement SEPA',
            'card' => 'Carte bancaire',
            default => ucfirst($method),
        };
    }
}

if (!function_exists('get_contract_status_label')) {
    /**
     * Get human-readable contract status label
     */
    function get_contract_status_label(string $status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'active' => 'Actif',
            'suspended' => 'Suspendu',
            'terminated' => 'Résilié',
            'expired' => 'Expiré',
            default => ucfirst($status),
        };
    }
}

if (!function_exists('get_invoice_status_label')) {
    /**
     * Get human-readable invoice status label
     */
    function get_invoice_status_label(string $status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'paid' => 'Payée',
            'partial' => 'Partiellement payée',
            'overdue' => 'En retard',
            'cancelled' => 'Annulée',
            default => ucfirst($status),
        };
    }
}

if (!function_exists('get_loyalty_tier_name')) {
    /**
     * Get loyalty tier name
     */
    function get_loyalty_tier_name(string $tier): string
    {
        return match ($tier) {
            'bronze' => 'Bronze',
            'silver' => 'Argent',
            'gold' => 'Or',
            'platinum' => 'Platine',
            default => ucfirst($tier),
        };
    }
}

if (!function_exists('get_loyalty_tier_discount')) {
    /**
     * Get loyalty tier discount percentage
     */
    function get_loyalty_tier_discount(string $tier): float
    {
        return match ($tier) {
            'bronze' => 0,
            'silver' => 5,
            'gold' => 10,
            'platinum' => 15,
            default => 0,
        };
    }
}

if (!function_exists('days_between')) {
    /**
     * Get days between two dates
     */
    function days_between($date1, $date2): int
    {
        $d1 = is_string($date1) ? \Carbon\Carbon::parse($date1) : $date1;
        $d2 = is_string($date2) ? \Carbon\Carbon::parse($date2) : $date2;

        return abs($d1->diffInDays($d2));
    }
}

if (!function_exists('is_business_hours')) {
    /**
     * Check if current time is within business hours (8h-20h)
     */
    function is_business_hours(): bool
    {
        $hour = now()->hour;
        return $hour >= 8 && $hour < 20;
    }
}
