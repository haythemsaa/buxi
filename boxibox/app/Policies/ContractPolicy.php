<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
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
    public function view(?User $user, Contract $contract): bool
    {
        if (!$user) {
            return false;
        }

        // Admin can view all
        if ($user->userable_type === User::class) {
            return true;
        }

        // Customer can only view their own contracts
        if ($user->userable_type === \App\Models\Customer::class) {
            return $contract->customer_id === $user->userable_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Only admin/staff can create contracts
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Contract $contract): bool
    {
        // Only admin/staff can update contracts
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Contract $contract): bool
    {
        // Only admin/staff can delete contracts
        // And only if not active
        return $user
            && $user->userable_type === User::class
            && !in_array($contract->status, ['active', 'pending']);
    }

    /**
     * Determine whether the user can request termination.
     */
    public function requestTermination(?User $user, Contract $contract): bool
    {
        if (!$user) {
            return false;
        }

        // Contract must be active
        if ($contract->status !== 'active') {
            return false;
        }

        // Customer can request termination of their own contract
        if ($user->userable_type === \App\Models\Customer::class) {
            return $contract->customer_id === $user->userable_id;
        }

        // Admin can also request termination
        return $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can approve termination.
     */
    public function approveTermination(?User $user, Contract $contract): bool
    {
        // Only admin/staff can approve termination
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Contract $contract): bool
    {
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Contract $contract): bool
    {
        return $user && $user->userable_type === User::class;
    }
}
