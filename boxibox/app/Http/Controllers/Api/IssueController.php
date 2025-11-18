<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerIssue;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IssueController extends Controller
{
    /**
     * Get customer's issues
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $issues = CustomerIssue::where('customer_id', $customer->id)
            ->with('contract')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($issue) {
                return [
                    'id' => $issue->id,
                    'issue_number' => $issue->issue_number,
                    'type' => $issue->type,
                    'type_label' => $issue->type_label,
                    'subject' => $issue->subject,
                    'priority' => $issue->priority,
                    'priority_label' => $issue->priority_label,
                    'status' => $issue->status,
                    'status_label' => $issue->status_label,
                    'created_at' => $issue->created_at,
                    'resolved_at' => $issue->resolved_at,
                ];
            });

        return response()->json([
            'issues' => $issues,
        ]);
    }

    /**
     * Get issue details
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $customer = $request->user();

        $issue = CustomerIssue::where('customer_id', $customer->id)
            ->with('contract')
            ->find($id);

        if (!$issue) {
            return response()->json([
                'message' => 'Signalement non trouvé',
            ], 404);
        }

        return response()->json([
            'issue' => [
                'id' => $issue->id,
                'issue_number' => $issue->issue_number,
                'type' => $issue->type,
                'type_label' => $issue->type_label,
                'subject' => $issue->subject,
                'description' => $issue->description,
                'priority' => $issue->priority,
                'priority_label' => $issue->priority_label,
                'status' => $issue->status,
                'status_label' => $issue->status_label,
                'resolution_notes' => $issue->resolution_notes,
                'resolved_at' => $issue->resolved_at,
                'created_at' => $issue->created_at,
                'updated_at' => $issue->updated_at,
                'contract' => $issue->contract ? [
                    'id' => $issue->contract->id,
                    'contract_number' => $issue->contract->contract_number,
                ] : null,
            ],
        ]);
    }

    /**
     * Create a new issue
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_id' => 'nullable|exists:contracts,id',
            'type' => ['required', Rule::in(['access', 'maintenance', 'billing', 'security', 'other'])],
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        $customer = $request->user();

        // Verify contract belongs to customer if provided
        if (isset($validated['contract_id'])) {
            $contractBelongsToCustomer = $customer->contracts()
                ->where('id', $validated['contract_id'])
                ->exists();

            if (!$contractBelongsToCustomer) {
                return response()->json([
                    'message' => 'Le contrat spécifié n\'appartient pas à ce client',
                ], 403);
            }
        }

        $issue = CustomerIssue::create([
            'customer_id' => $customer->id,
            'contract_id' => $validated['contract_id'] ?? null,
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? 'medium',
        ]);

        return response()->json([
            'message' => 'Signalement créé avec succès',
            'issue' => [
                'id' => $issue->id,
                'issue_number' => $issue->issue_number,
                'type' => $issue->type,
                'subject' => $issue->subject,
                'status' => $issue->status,
                'created_at' => $issue->created_at,
            ],
        ], 201);
    }
}
