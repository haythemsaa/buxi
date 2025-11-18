<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = 1; // TODO: Get from authenticated user's tenant

        $query = Customer::where('tenant_id', $tenantId)
            ->withCount(['contracts as active_contracts_count' => function ($query) {
                $query->where('status', 'active');
            }]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('customer_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'type', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:individual,professional',
            'company_name' => 'required_if:type,professional|nullable|string|max:255',
            'siret' => 'nullable|string|max:14',
            'vat_number' => 'nullable|string|max:20',
            'first_name' => 'required_if:type,individual|nullable|string|max:255',
            'last_name' => 'required_if:type,individual|nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'required|string',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $validated['tenant_id'] = 1; // TODO: Get from authenticated user's tenant

        // Generate customer number
        $latestCustomer = Customer::where('tenant_id', $validated['tenant_id'])
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $latestCustomer ? (intval(substr($latestCustomer->customer_number, 3)) + 1) : 1;
        $validated['customer_number'] = 'CL' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        $validated['status'] = 'active';

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Client créé avec succès.');
    }

    public function show(Customer $customer): Response
    {
        $customer->load([
            'contracts.box',
            'contracts' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'type' => 'required|in:individual,professional',
            'company_name' => 'required_if:type,professional|nullable|string|max:255',
            'siret' => 'nullable|string|max:14',
            'vat_number' => 'nullable|string|max:20',
            'first_name' => 'required_if:type,individual|nullable|string|max:255',
            'last_name' => 'required_if:type,individual|nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'required|string',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Client modifié avec succès.');
    }

    public function destroy(Customer $customer)
    {
        // Check if customer has active contracts
        if ($customer->contracts()->where('status', 'active')->exists()) {
            return redirect()->route('customers.index')
                ->with('error', 'Impossible de supprimer un client avec des contrats actifs.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Client supprimé avec succès.');
    }
}
