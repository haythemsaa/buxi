<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Invoice $invoice): bool
    {
        if (!$user) {
            return false;
        }

        // Admin can view all
        if ($user->userable_type === User::class) {
            return true;
        }

        // Customer can only view their own invoices
        if ($user->userable_type === \App\Models\Customer::class) {
            return $invoice->customer_id === $user->userable_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Only admin/staff can create invoices
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Invoice $invoice): bool
    {
        // Only admin/staff can update invoices
        // And only if not paid
        return $user
            && $user->userable_type === User::class
            && $invoice->status !== 'paid';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Invoice $invoice): bool
    {
        // Only admin/staff can delete invoices
        // And only if draft or pending
        return $user
            && $user->userable_type === User::class
            && in_array($invoice->status, ['draft', 'pending']);
    }

    /**
     * Determine whether the user can download the invoice PDF.
     */
    public function download(?User $user, Invoice $invoice): bool
    {
        if (!$user) {
            return false;
        }

        // Admin can download all
        if ($user->userable_type === User::class) {
            return true;
        }

        // Customer can download their own invoices
        if ($user->userable_type === \App\Models\Customer::class) {
            return $invoice->customer_id === $user->userable_id;
        }

        return false;
    }

    /**
     * Determine whether the user can mark as paid.
     */
    public function markAsPaid(?User $user, Invoice $invoice): bool
    {
        // Only admin/staff can mark invoices as paid
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Invoice $invoice): bool
    {
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Invoice $invoice): bool
    {
        return $user && $user->userable_type === User::class;
    }
}
