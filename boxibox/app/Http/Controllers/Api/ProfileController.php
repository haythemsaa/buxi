<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Update customer profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $customer = $request->user();

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'customer' => [
                'id' => $customer->id,
                'customer_number' => $customer->customer_number,
                'type' => $customer->type,
                'name' => $customer->type === 'professional'
                    ? $customer->company_name
                    : "{$customer->first_name} {$customer->last_name}",
                'email' => $customer->email,
                'phone' => $customer->phone,
                'phone_secondary' => $customer->phone_secondary,
                'address' => $customer->address,
                'postal_code' => $customer->postal_code,
                'city' => $customer->city,
                'country' => $customer->country,
            ],
        ]);
    }

    /**
     * Update password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $customer = $request->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($validated['current_password'], $customer->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect',
                'errors' => [
                    'current_password' => ['Le mot de passe actuel est incorrect'],
                ],
            ], 422);
        }

        $customer->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Delete all tokens to force re-login
        $customer->tokens()->delete();

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès. Veuillez vous reconnecter.',
        ]);
    }

    /**
     * Get customer statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $customer = $request->user();

        $activeContracts = $customer->contracts()->where('status', 'active')->count();
        $totalContracts = $customer->contracts()->count();

        $totalPaid = $customer->contracts()
            ->with('invoices.payments')
            ->get()
            ->pluck('invoices')
            ->flatten()
            ->pluck('payments')
            ->flatten()
            ->where('status', 'succeeded')
            ->sum('amount');

        $pendingInvoices = $customer->contracts()
            ->with('invoices')
            ->get()
            ->pluck('invoices')
            ->flatten()
            ->where('status', 'pending')
            ->count();

        $overdueInvoices = $customer->contracts()
            ->with('invoices')
            ->get()
            ->pluck('invoices')
            ->flatten()
            ->where('status', 'overdue')
            ->count();

        return response()->json([
            'statistics' => [
                'active_contracts' => $activeContracts,
                'total_contracts' => $totalContracts,
                'total_paid' => $totalPaid,
                'pending_invoices' => $pendingInvoices,
                'overdue_invoices' => $overdueInvoices,
            ],
        ]);
    }
}
