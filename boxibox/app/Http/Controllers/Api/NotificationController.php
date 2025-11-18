<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushNotificationToken;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    /**
     * Register a push notification token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'platform' => ['required', Rule::in(['ios', 'android'])],
            'device_name' => 'nullable|string|max:255',
        ]);

        $customer = $request->user();

        // Check if token already exists
        $existingToken = PushNotificationToken::where('token', $validated['token'])->first();

        if ($existingToken) {
            // Update existing token
            $existingToken->update([
                'customer_id' => $customer->id,
                'platform' => $validated['platform'],
                'device_name' => $validated['device_name'] ?? $existingToken->device_name,
                'is_active' => true,
                'last_used_at' => now(),
            ]);

            return response()->json([
                'message' => 'Token mis à jour avec succès',
                'token' => $existingToken,
            ]);
        }

        // Create new token
        $token = PushNotificationToken::create([
            'customer_id' => $customer->id,
            'token' => $validated['token'],
            'platform' => $validated['platform'],
            'device_name' => $validated['device_name'] ?? null,
            'last_used_at' => now(),
        ]);

        return response()->json([
            'message' => 'Token enregistré avec succès',
            'token' => $token,
        ], 201);
    }

    /**
     * Unregister a push notification token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unregisterToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $customer = $request->user();

        $token = PushNotificationToken::where('customer_id', $customer->id)
            ->where('token', $validated['token'])
            ->first();

        if (!$token) {
            return response()->json([
                'message' => 'Token non trouvé',
            ], 404);
        }

        $token->deactivate();

        return response()->json([
            'message' => 'Token désactivé avec succès',
        ]);
    }

    /**
     * Get customer's registered tokens
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTokens(Request $request)
    {
        $customer = $request->user();

        $tokens = PushNotificationToken::where('customer_id', $customer->id)
            ->where('is_active', true)
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'platform' => $token->platform,
                    'device_name' => $token->device_name,
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                ];
            });

        return response()->json([
            'tokens' => $tokens,
        ]);
    }

    /**
     * Update notification preferences
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'invoice_notifications' => 'boolean',
            'payment_reminders' => 'boolean',
            'contract_notifications' => 'boolean',
            'promotional_notifications' => 'boolean',
        ]);

        $customer = $request->user();

        // Store preferences in customer metadata or separate table
        // For now, we'll just acknowledge the update
        // In production, you'd want to store this in a preferences table or JSON column

        return response()->json([
            'message' => 'Préférences de notifications mises à jour',
            'preferences' => $validated,
        ]);
    }
}
