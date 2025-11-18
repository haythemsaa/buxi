<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Reservation;
use App\Models\Site;
use App\Services\PriceCalculatorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReservationController extends Controller
{
    protected $priceCalculator;

    public function __construct(PriceCalculatorService $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * Page de recherche de boxes
     */
    public function index()
    {
        $sites = Site::with(['buildings.floors.boxes' => function($query) {
            $query->where('status', 'available');
        }])->get();

        return Inertia::render('Reservations/Index', [
            'sites' => $sites,
        ]);
    }

    /**
     * Rechercher des boxes disponibles
     */
    public function search(Request $request)
    {
        $query = Box::query()
            ->with(['floor.building.site'])
            ->where('status', 'available');

        // Filtres
        if ($request->site_id) {
            $query->whereHas('floor.building.site', function($q) use ($request) {
                $q->where('id', $request->site_id);
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

        if ($request->vehicle_access) {
            $query->where('vehicle_access', true);
        }

        $boxes = $query->get()->map(function($box) use ($request) {
            $duration = $request->duration_months ?? 1;
            $pricing = $this->priceCalculator->calculatePrice($box, $duration);

            return [
                'id' => $box->id,
                'number' => $box->number,
                'volume' => $box->volume,
                'surface' => $box->surface,
                'dimensions' => "{$box->length}x{$box->width}x{$box->height}m",
                'floor' => $box->floor->name,
                'building' => $box->floor->building->name,
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
     * Calculer le prix
     */
    public function calculatePrice(Request $request)
    {
        $validated = $request->validate([
            'box_id' => 'required|exists:boxes,id',
            'duration_months' => 'required|integer|min:1',
            'promo_code' => 'nullable|string',
            'insurance' => 'nullable|boolean',
        ]);

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
     * Comparer des boxes
     */
    public function compare(Request $request)
    {
        $validated = $request->validate([
            'box_ids' => 'required|array|min:2|max:4',
            'box_ids.*' => 'exists:boxes,id',
            'duration_months' => 'required|integer|min:1',
        ]);

        $comparisons = $this->priceCalculator->compareBoxes(
            $validated['box_ids'],
            $validated['duration_months']
        );

        return response()->json(['comparisons' => $comparisons]);
    }

    /**
     * Formulaire de réservation
     */
    public function create(Request $request)
    {
        $box = Box::with(['floor.building.site'])->findOrFail($request->box_id);

        return Inertia::render('Reservations/Create', [
            'box' => $box,
            'duration' => $request->duration ?? 1,
        ]);
    }

    /**
     * Créer une réservation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'box_id' => 'required|exists:boxes,id',
            'start_date' => 'required|date|after_or_equal:today',
            'duration_months' => 'required|integer|min:1',
            'promo_code' => 'nullable|string',
            'insurance' => 'nullable|boolean',
            'guest_email' => 'required_without:customer_id|email',
            'guest_first_name' => 'required_without:customer_id|string',
            'guest_last_name' => 'required_without:customer_id|string',
            'guest_phone' => 'required_without:customer_id|string',
            'notes' => 'nullable|string',
        ]);

        $box = Box::findOrFail($validated['box_id']);

        // Vérifier disponibilité
        if ($box->status !== 'available') {
            return back()->withErrors(['box_id' => 'Ce box n\'est plus disponible']);
        }

        // Calculer les prix
        $promotion = null;
        if (!empty($validated['promo_code'])) {
            $promotion = $this->priceCalculator->validatePromoCode(
                $validated['promo_code'],
                auth()->id()
            );
        }

        $pricing = $this->priceCalculator->calculatePrice(
            $box,
            $validated['duration_months'],
            $promotion,
            ['insurance' => $validated['insurance'] ?? false]
        );

        // Créer la réservation
        $reservation = Reservation::create([
            'customer_id' => auth()->id(),
            'box_id' => $box->id,
            'site_id' => $box->floor->building->site_id,
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_first_name' => $validated['guest_first_name'] ?? null,
            'guest_last_name' => $validated['guest_last_name'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
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
            'status' => 'pending',
        ]);

        // Réserver le box temporairement
        $box->update(['status' => 'reserved']);

        // Incrémenter utilisations promo
        if ($promotion) {
            $promotion->incrementUses();
        }

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Réservation créée avec succès !');
    }

    /**
     * Afficher une réservation
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['box.floor.building.site', 'promotion']);

        return Inertia::render('Reservations/Show', [
            'reservation' => $reservation,
        ]);
    }
}
