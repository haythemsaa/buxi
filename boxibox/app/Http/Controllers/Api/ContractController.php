<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContractTerminationRequest;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Get customer's contracts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $contracts = $customer->contracts()
            ->with(['box.floor.building.site'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'start_date' => $contract->start_date,
                    'end_date' => $contract->end_date,
                    'status' => $contract->status,
                    'status_label' => $this->getStatusLabel($contract->status),
                    'total_monthly_amount' => $contract->total_monthly_amount,
                    'payment_method' => $contract->payment_method,
                    'payment_day' => $contract->payment_day,
                    'access_code' => $contract->access_code,
                    'box' => [
                        'id' => $contract->box->id,
                        'number' => $contract->box->number,
                        'volume' => round($contract->box->volume),
                        'surface' => $contract->box->surface,
                        'floor' => $contract->box->floor->name,
                        'building' => $contract->box->floor->building->name,
                        'site' => $contract->box->floor->building->site->name,
                        'site_address' => $contract->box->floor->building->site->address,
                        'site_city' => $contract->box->floor->building->site->city,
                    ],
                ];
            });

        return response()->json([
            'contracts' => $contracts,
        ]);
    }

    /**
     * Get contract details
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $customer = $request->user();

        $contract = $customer->contracts()
            ->with(['box.floor.building.site'])
            ->findOrFail($id);

        return response()->json([
            'contract' => [
                'id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'start_date' => $contract->start_date,
                'end_date' => $contract->end_date,
                'status' => $contract->status,
                'status_label' => $this->getStatusLabel($contract->status),
                'initial_duration_months' => $contract->initial_duration_months,
                'price_monthly_ht' => $contract->price_monthly_ht,
                'tax_rate' => $contract->tax_rate,
                'insurance_monthly' => $contract->insurance_monthly,
                'total_monthly_amount' => $contract->total_monthly_amount,
                'deposit_amount' => $contract->deposit_amount,
                'payment_method' => $contract->payment_method,
                'payment_method_label' => $this->getPaymentMethodLabel($contract->payment_method),
                'payment_day' => $contract->payment_day,
                'access_code' => $contract->access_code,
                'notes' => $contract->notes,
                'box' => [
                    'id' => $contract->box->id,
                    'number' => $contract->box->number,
                    'volume' => round($contract->box->volume),
                    'surface' => $contract->box->surface,
                    'length' => $contract->box->length,
                    'width' => $contract->box->width,
                    'height' => $contract->box->height,
                    'climate_controlled' => $contract->box->climate_controlled,
                    'ground_floor' => $contract->box->ground_floor,
                    'vehicle_access' => $contract->box->vehicle_access,
                    'has_electricity' => $contract->box->has_electricity,
                    'floor' => $contract->box->floor->name,
                    'building' => $contract->box->floor->building->name,
                    'site' => [
                        'id' => $contract->box->floor->building->site->id,
                        'name' => $contract->box->floor->building->site->name,
                        'address' => $contract->box->floor->building->site->address,
                        'postal_code' => $contract->box->floor->building->site->postal_code,
                        'city' => $contract->box->floor->building->site->city,
                        'phone' => $contract->box->floor->building->site->phone,
                        'email' => $contract->box->floor->building->site->email,
                        'gps_latitude' => $contract->box->floor->building->site->gps_latitude,
                        'gps_longitude' => $contract->box->floor->building->site->gps_longitude,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get contract status label
     *
     * @param string $status
     * @return string
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'draft' => 'Brouillon',
            'active' => 'Actif',
            'suspended' => 'Suspendu',
            'terminated' => 'Résilié',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get payment method label
     *
     * @param string $method
     * @return string
     */
    private function getPaymentMethodLabel($method)
    {
        $labels = [
            'sepa' => 'Prélèvement SEPA',
            'card' => 'Carte bancaire',
            'transfer' => 'Virement bancaire',
            'cash' => 'Espèces',
            'check' => 'Chèque',
        ];

        return $labels[$method] ?? $method;
    }

    /**
     * Request contract termination
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestTermination(Request $request, $id)
    {
        $validated = $request->validate([
            'requested_termination_date' => 'required|date|after:today',
            'reason' => 'required|string|min:10',
        ]);

        $customer = $request->user();

        // Verify contract belongs to customer
        $contract = $customer->contracts()->findOrFail($id);

        // Check if contract is already terminated
        if ($contract->status === 'terminated') {
            return response()->json([
                'message' => 'Ce contrat est déjà résilié',
            ], 422);
        }

        // Check if there's already a pending request
        $existingRequest = ContractTerminationRequest::where('contract_id', $id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'message' => 'Une demande de résiliation est déjà en cours pour ce contrat',
            ], 422);
        }

        // Create termination request
        $terminationRequest = ContractTerminationRequest::create([
            'contract_id' => $id,
            'customer_id' => $customer->id,
            'requested_termination_date' => $validated['requested_termination_date'],
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Demande de résiliation envoyée avec succès',
            'termination_request' => [
                'id' => $terminationRequest->id,
                'contract_number' => $contract->contract_number,
                'requested_termination_date' => $terminationRequest->requested_termination_date,
                'status' => $terminationRequest->status,
                'status_label' => $terminationRequest->status_label,
                'created_at' => $terminationRequest->created_at,
            ],
        ], 201);
    }

    /**
     * Get termination requests for customer's contracts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function terminationRequests(Request $request)
    {
        $customer = $request->user();

        $requests = ContractTerminationRequest::where('customer_id', $customer->id)
            ->with('contract')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'contract_number' => $req->contract->contract_number,
                    'requested_termination_date' => $req->requested_termination_date,
                    'approved_termination_date' => $req->approved_termination_date,
                    'status' => $req->status,
                    'status_label' => $req->status_label,
                    'reason' => $req->reason,
                    'admin_notes' => $req->admin_notes,
                    'created_at' => $req->created_at,
                    'processed_at' => $req->processed_at,
                ];
            });

        return response()->json([
            'termination_requests' => $requests,
        ]);
    }
}
