<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Promotion;
use App\Models\User;

class PromotionPolicy
{
    /**
     * Determine if the user can view any promotions.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view public promotions
        return true;
    }

    /**
     * Determine if the user can view the promotion.
     */
    public function view(?User $user, Promotion $promotion): bool
    {
        // Anyone can view public promotions
        if ($promotion->is_public) {
            return true;
        }

        // Only admins can view private promotions
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine if the user can create promotions.
     */
    public function create(?User $user): bool
    {
        // Only admins can create promotions
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine if the user can update the promotion.
     */
    public function update(?User $user, Promotion $promotion): bool
    {
        // Only admins can update promotions
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine if the user can delete the promotion.
     */
    public function delete(?User $user, Promotion $promotion): bool
    {
        // Only admins can delete promotions
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine if the user can use this promotion.
     */
    public function use(?User $user, Promotion $promotion): bool
    {
        // Promotion must be active and valid
        if (!$promotion->isValid()) {
            return false;
        }

        // If promotion requires being a new customer
        if ($promotion->new_customers_only) {
            // Guest users are considered new customers
            if (!$user) {
                return true;
            }

            // Check if customer has no contracts
            if ($user->userable_type === Customer::class) {
                $customer = $user->userable;
                return $customer->contracts()->count() === 0;
            }

            return false;
        }

        // If promotion is online only
        if ($promotion->online_only) {
            return true; // Assuming we're in an online context
        }

        return true;
    }

    /**
     * Determine if the user can validate a promotion code.
     */
    public function validate(?User $user): bool
    {
        // Anyone can validate promotion codes
        return true;
    }
}
