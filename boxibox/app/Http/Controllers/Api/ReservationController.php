<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalculatePriceRequest;
use App\Http\Requests\Api\SearchBoxesRequest;
use App\Http\Requests\Api\StoreReservationRequest;
use App\Models\Box;
use App\Models\Reservation;
use App\Services\PriceCalculatorService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected $priceCalculator;

    public function __construct(PriceCalculatorService $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * Rechercher des boxes disponibles
     */
    public function search(SearchBoxesRequest $request)
    {
        $query = Box::query()
            ->with(['floor.building.site'])
            ->where('status', 'available');

        // Filtres
        if ($request->site_id) {
            $query->whereHas('floor.building', function($q) use ($request) {
                $q->where('site_id', $request->site_id);
            });
        }

        if ($request->min_volume) {
            $query->where('volume', '>=', $request->min_volume);
        }

        if ($request->max_volume) {
            $query->where('volume', '<=', $request->max_volume);
        }

        if ($request->climate_controlled) {
            $query->where('climate_controlled', true);
        }

        if ($request->ground_floor) {
            $query->where('ground_floor', true);
        }

        $boxes = $query->limit(20)->get()->map(function($box) use ($request) {
            $duration = $request->duration_months ?? 1;
            $pricing = $this->priceCalculator->calculatePrice($box, $duration);

            return [
                'id' => $box->id,
                'number' => $box->number,
                'volume' => round($box->volume, 2),
                'surface' => $box->surface,
                'dimensions' => "{$box->length}x{$box->width}x{$box->height}m",
                'site' => [
                    'id' => $box->floor->building->site->id,
                    'name' => $box->floor->building->site->name,
                    'address' => $box->floor->building->site->address,
                    'city' => $box->floor->building->site->city,
                ],
                'features' => [
                    'climate_controlled' => $box->climate_controlled,
                    'ground_floor' => $box->ground_floor,
                    'vehicle_access' => $box->vehicle_access,
                    'has_electricity' => $box->has_electricity,
                ],
                'pricing' => $pricing,
            ];
        });

        return response()->json(['boxes' => $boxes]);
    }

    /**
     * Calculer le prix pour une réservation
     */
    public function calculatePrice(CalculatePriceRequest $request)
    {
        $validated = $request->validated();

        $box = Box::findOrFail($validated['box_id']);
        $promotion = null;

        if (!empty($validated['promo_code'])) {
            $promotion = $this->priceCalculator->validatePromoCode(
                $validated['promo_code'],
                auth()->id()
            );

            if (!$promotion) {
                return response()->json([
                    'error' => 'Code promo invalide ou expiré'
                ], 422);
            }
        }

        $pricing = $this->priceCalculator->calculatePrice(
            $box,
            $validated['duration_months'],
            $promotion,
            ['insurance' => $validated['insurance'] ?? false]
        );

        return response()->json([
            'pricing' => $pricing,
            'promotion' => $promotion ? [
                'code' => $promotion->code,
                'name' => $promotion->name,
                'description' => $promotion->description,
            ] : null,
        ]);
    }

    /**
     * Créer une réservation depuis l'app mobile
     */
    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();

        $customer = $request->user();
        $box = Box::findOrFail($validated['box_id']);

        // Vérifier disponibilité
        if ($box->status !== 'available') {
            return response()->json([
                'message' => 'Ce box n\'est plus disponible'
            ], 422);
        }

        // Calculer prix
        $promotion = null;
        if (!empty($validated['promo_code'])) {
            $promotion = $this->priceCalculator->validatePromoCode(
                $validated['promo_code'],
                $customer->id
            );
        }

        $pricing = $this->priceCalculator->calculatePrice(
            $box,
            $validated['duration_months'],
            $promotion,
            ['insurance' => $validated['insurance'] ?? false]
        );

        // Créer réservation
        $reservation = Reservation::create([
            'customer_id' => $customer->id,
            'box_id' => $box->id,
            'site_id' => $box->floor->building->site_id,
            'start_date' => $validated['start_date'],
            'duration_months' => $validated['duration_months'],
            'monthly_price_ht' => $pricing['monthly_price_ht'],
            'tax_rate' => $pricing['tax_rate'],
            'deposit_amount' => $pricing['deposit_amount'],
            'first_payment' => $pricing['first_payment'],
            'promotion_id' => $promotion?->id,
            'discount_amount' => $pricing['discount_amount'],
            'requires_insurance' => $validated['insurance'] ?? false,
            'insurance_monthly' => $pricing['insurance_monthly'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Réserver le box
        $box->update(['status' => 'reserved']);

        if ($promotion) {
            $promotion->incrementUses();
        }

        return response()->json([
            'message' => 'Réservation créée avec succès',
            'reservation' => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'box_number' => $box->number,
                'site_name' => $box->floor->building->site->name,
                'start_date' => $reservation->start_date,
                'duration_months' => $reservation->duration_months,
                'first_payment' => $reservation->first_payment,
                'monthly_price_ttc' => $pricing['total_monthly_ttc'],
                'expires_at' => $reservation->expires_at,
                'status' => $reservation->status,
            ],
        ], 201);
    }

    /**
     * Liste des réservations du client
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $reservations = Reservation::where('customer_id', $customer->id)
            ->with(['box.floor.building.site', 'promotion'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($reservation) {
                return [
                    'id' => $reservation->id,
                    'reservation_number' => $reservation->reservation_number,
                    'box_number' => $reservation->box->number,
                    'site_name' => $reservation->box->floor->building->site->name,
                    'start_date' => $reservation->start_date,
                    'duration_months' => $reservation->duration_months,
                    'monthly_price_ht' => $reservation->monthly_price_ht,
                    'total_monthly_ttc' => $reservation->monthly_price_ht * (1 + $reservation->tax_rate / 100) + ($reservation->insurance_monthly ?? 0),
                    'status' => $reservation->status,
                    'expires_at' => $reservation->expires_at,
                    'created_at' => $reservation->created_at,
                ];
            });

        return response()->json(['reservations' => $reservations]);
    }

    /**
     * Détails d'une réservation
     */
    public function show(Request $request, $id)
    {
        $customer = $request->user();

        $reservation = Reservation::where('customer_id', $customer->id)
            ->with(['box.floor.building.site', 'promotion'])
            ->find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Réservation non trouvée'], 404);
        }

        return response()->json([
            'reservation' => [
                'id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'start_date' => $reservation->start_date,
                'end_date' => $reservation->end_date,
                'duration_months' => $reservation->duration_months,
                'monthly_price_ht' => $reservation->monthly_price_ht,
                'tax_rate' => $reservation->tax_rate,
                'deposit_amount' => $reservation->deposit_amount,
                'first_payment' => $reservation->first_payment,
                'discount_amount' => $reservation->discount_amount,
                'insurance_monthly' => $reservation->insurance_monthly,
                'status' => $reservation->status,
                'expires_at' => $reservation->expires_at,
                'notes' => $reservation->notes,
                'box' => [
                    'number' => $reservation->box->number,
                    'volume' => $reservation->box->volume,
                    'surface' => $reservation->box->surface,
                    'dimensions' => "{$reservation->box->length}x{$reservation->box->width}x{$reservation->box->height}m",
                    'climate_controlled' => $reservation->box->climate_controlled,
                ],
                'site' => [
                    'name' => $reservation->box->floor->building->site->name,
                    'address' => $reservation->box->floor->building->site->address,
                    'city' => $reservation->box->floor->building->site->city,
                    'phone' => $reservation->box->floor->building->site->phone,
                ],
                'promotion' => $reservation->promotion ? [
                    'code' => $reservation->promotion->code,
                    'name' => $reservation->promotion->name,
                ] : null,
                'created_at' => $reservation->created_at,
            ],
        ]);
    }

    /**
     * Annuler une réservation
     */
    public function cancel(Request $request, $id)
    {
        $customer = $request->user();

        $reservation = Reservation::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Réservation non trouvée ou déjà traitée'], 404);
        }

        $reservation->cancel();

        // Libérer le box
        $reservation->box->update(['status' => 'available']);

        return response()->json(['message' => 'Réservation annulée avec succès']);
    }
}
