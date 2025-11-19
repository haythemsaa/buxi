<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Display customer profile edit form
     */
    public function edit(Request $request)
    {
        $customer = $request->user()->customer;

        return Inertia::render('Customer/Profile/Edit', [
            'customer' => $customer,
            'user' => $request->user(),
        ]);
    }

    /**
     * Update customer profile
     */
    public function update(Request $request)
    {
        $customer = $request->user()->customer;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
        ]);

        $customer->update($validated);

        // Update user email if changed
        if ($request->user()->email !== $validated['email']) {
            $request->user()->update(['email' => $validated['email']]);
        }

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Update customer password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Mot de passe mis à jour avec succès.');
    }
}
