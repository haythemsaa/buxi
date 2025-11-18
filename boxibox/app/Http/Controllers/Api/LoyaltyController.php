<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    /**
     * Obtenir le solde de points du client
     */
    public function balance(Request $request)
    {
        $customer = $request->user();

        $loyalty = LoyaltyPoint::firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'points' => 0,
                'points_earned' => 0,
                'points_spent' => 0,
                'tier' => 'bronze',
            ]
        );

        return response()->json([
            'loyalty' => [
                'points' => $loyalty->points,
                'points_earned' => $loyalty->points_earned,
                'points_spent' => $loyalty->points_spent,
                'tier' => $loyalty->tier,
                'tier_label' => $loyalty->tier_label,
                'tier_discount' => $loyalty->tier_discount,
                'points_to_next_tier' => $loyalty->points_to_next_tier,
            ],
        ]);
    }

    /**
     * Historique des transactions de points
     */
    public function history(Request $request)
    {
        $customer = $request->user();

        $transactions = $customer->loyaltyTransactions()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'type_label' => $transaction->type_label,
                    'points' => $transaction->points,
                    'description' => $transaction->description,
                    'expires_at' => $transaction->expires_at,
                    'created_at' => $transaction->created_at,
                ];
            });

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * Informations sur le programme de fidélité
     */
    public function info()
    {
        return response()->json([
            'program' => [
                'name' => 'Boxibox Loyalty',
                'currency' => 'points',
                'tiers' => [
                    [
                        'name' => 'Bronze',
                        'min_points' => 0,
                        'max_points' => 999,
                        'discount' => 0,
                        'benefits' => [
                            'Points sur chaque paiement',
                            'Offres exclusives',
                        ],
                    ],
                    [
                        'name' => 'Argent',
                        'min_points' => 1000,
                        'max_points' => 4999,
                        'discount' => 5,
                        'benefits' => [
                            'Tous les avantages Bronze',
                            '-5% sur les options',
                            'Priorité support client',
                        ],
                    ],
                    [
                        'name' => 'Or',
                        'min_points' => 5000,
                        'max_points' => 9999,
                        'discount' => 10,
                        'benefits' => [
                            'Tous les avantages Argent',
                            '-10% sur les options',
                            'Upgrade box gratuit (selon disponibilité)',
                            'Accès anticipé aux promotions',
                        ],
                    ],
                    [
                        'name' => 'Platine',
                        'min_points' => 10000,
                        'max_points' => null,
                        'discount' => 15,
                        'benefits' => [
                            'Tous les avantages Or',
                            '-15% sur les options',
                            'Gestionnaire de compte dédié',
                            'Services premium inclus',
                            'Invitations événements VIP',
                        ],
                    ],
                ],
                'earning_rules' => [
                    'Nouveau contrat' => 100,
                    'Par mois de location' => 10,
                    'Parrainage réussi' => 50,
                    'Avis client' => 20,
                    'Paiement à temps (12 mois)' => 100,
                ],
                'redemption' => [
                    '1000 points' => '10€ de réduction',
                    '2500 points' => '30€ de réduction',
                    '5000 points' => '60€ de réduction',
                    '10000 points' => '150€ de réduction',
                ],
                'expiration' => 'Les points expirent après 12 mois',
            ],
        ]);
    }
}
