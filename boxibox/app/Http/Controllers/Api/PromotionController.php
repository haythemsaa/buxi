<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * Liste des promotions publiques actives
     */
    public function index()
    {
        $promotions = Promotion::active()
            ->public()
            ->orderBy('priority', 'desc')
            ->get()
            ->map(function($promo) {
                return [
                    'id' => $promo->id,
                    'code' => $promo->code,
                    'name' => $promo->name,
                    'description' => $promo->description,
                    'discount_type' => $promo->discount_type,
                    'discount_value' => $promo->discount_value,
                    'free_months' => $promo->free_months,
                    'valid_from' => $promo->valid_from,
                    'valid_until' => $promo->valid_until,
                    'online_only' => $promo->online_only,
                    'new_customers_only' => $promo->new_customers_only,
                ];
            });

        return response()->json(['promotions' => $promotions]);
    }

    /**
     * Valider un code promo
     */
    public function validate(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $promotion = Promotion::where('code', strtoupper($validated['code']))
            ->active()
            ->first();

        if (!$promotion) {
            return response()->json([
                'valid' => false,
                'message' => 'Code promo invalide ou expiré',
            ], 404);
        }

        $customer = $request->user();

        if (!$promotion->canBeUsedBy($customer)) {
            return response()->json([
                'valid' => false,
                'message' => 'Vous ne pouvez pas utiliser ce code promo',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'promotion' => [
                'code' => $promotion->code,
                'name' => $promotion->name,
                'description' => $promotion->description,
                'discount_type' => $promotion->discount_type,
                'discount_value' => $promotion->discount_value,
                'free_months' => $promotion->free_months,
            ],
        ]);
    }
}
