<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login with email and password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        // Check if customer is active
        if ($customer->status !== 'active') {
            return response()->json([
                'message' => 'Votre compte est inactif. Veuillez contacter le support.',
            ], 403);
        }

        // Delete old tokens
        $customer->tokens()->delete();

        // Create new token
        $token = $customer->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'customer' => [
                'id' => $customer->id,
                'customer_number' => $customer->customer_number,
                'type' => $customer->type,
                'name' => $customer->type === 'professional'
                    ? $customer->company_name
                    : "{$customer->first_name} {$customer->last_name}",
                'email' => $customer->email,
                'phone' => $customer->phone,
            ],
        ]);
    }

    /**
     * Logout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    /**
     * Get authenticated customer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $customer = $request->user();

        return response()->json([
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
                'status' => $customer->status,
            ],
        ]);
    }
}
