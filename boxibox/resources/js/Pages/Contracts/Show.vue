<template>
    <AppLayout>
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Contrat {{ contract.contract_number }}</h1>
                    <p class="text-gray-500 mt-1">
                        <span :class="getStatusBadgeClass(contract.status)">
                            {{ getStatusLabel(contract.status) }}
                        </span>
                    </p>
                </div>
                <div class="space-x-2">
                    <Link
                        v-if="contract.status === 'active'"
                        :href="route('contracts.edit', contract.id)"
                        class="btn btn-primary"
                    >
                        Modifier
                    </Link>
                    <Link :href="route('contracts.index')" class="btn btn-secondary">
                        Retour à la liste
                    </Link>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Contract Details -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Détails du contrat</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">N° Contrat</label>
                            <p class="text-lg text-gray-900 font-mono">{{ contract.contract_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Code d'accès</label>
                            <p class="text-lg text-gray-900 font-mono">{{ contract.access_code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Date de début</label>
                            <p class="text-lg text-gray-900">{{ formatDate(contract.start_date) }}</p>
                        </div>
                        <div v-if="contract.end_date">
                            <label class="block text-sm font-medium text-gray-500">Date de fin</label>
                            <p class="text-lg text-gray-900">{{ formatDate(contract.end_date) }}</p>
                        </div>
                        <div v-if="contract.initial_duration_months">
                            <label class="block text-sm font-medium text-gray-500">Durée initiale</label>
                            <p class="text-lg text-gray-900">{{ contract.initial_duration_months }} mois</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Mode de paiement</label>
                            <p class="text-lg text-gray-900">{{ getPaymentMethodLabel(contract.payment_method) }}</p>
                        </div>
                        <div v-if="contract.payment_day">
                            <label class="block text-sm font-medium text-gray-500">Jour de prélèvement</label>
                            <p class="text-lg text-gray-900">{{ contract.payment_day }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Client</h2>
                        <Link
                            :href="route('customers.show', contract.customer?.id)"
                            class="text-indigo-600 hover:text-indigo-900"
                        >
                            Voir la fiche →
                        </Link>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nom</label>
                            <p class="text-lg text-gray-900">
                                {{ contract.customer?.type === 'professional' ? contract.customer?.company_name : `${contract.customer?.first_name} ${contract.customer?.last_name}` }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="text-lg text-gray-900">{{ contract.customer?.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Téléphone</label>
                            <p class="text-lg text-gray-900">{{ contract.customer?.phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Box Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Box</h2>
                        <Link
                            :href="route('plan.index', { site_id: contract.box?.floor?.building?.site?.id })"
                            class="text-indigo-600 hover:text-indigo-900"
                        >
                            Voir le plan →
                        </Link>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">N° Box</label>
                            <p class="text-lg text-gray-900">{{ contract.box?.number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Dimensions</label>
                            <p class="text-lg text-gray-900">
                                {{ contract.box?.surface }}m² - {{ Math.round(contract.box?.volume) }}m³
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Site</label>
                            <p class="text-lg text-gray-900">{{ contract.box?.floor?.building?.site?.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Bâtiment</label>
                            <p class="text-lg text-gray-900">
                                {{ contract.box?.floor?.building?.name }} - {{ contract.box?.floor?.name }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Invoices -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">
                        Factures récentes ({{ contract.invoices?.length || 0 }})
                    </h2>
                    <div v-if="contract.invoices && contract.invoices.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Facture</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Montant TTC</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="invoice in contract.invoices" :key="invoice.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm font-mono">{{ invoice.invoice_number }}</td>
                                    <td class="px-4 py-2 text-sm">{{ formatDate(invoice.invoice_date) }}</td>
                                    <td class="px-4 py-2 text-sm font-medium">{{ formatCurrency(invoice.total_ttc) }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span :class="getInvoiceStatusClass(invoice.status)">
                                            {{ getInvoiceStatusLabel(invoice.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center text-gray-500 py-4">
                        Aucune facture
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Pricing -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Tarification</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Prix mensuel HT</span>
                            <span class="font-medium">{{ formatCurrency(contract.price_monthly_ht) }}</span>
                        </div>
                        <div v-if="contract.insurance_monthly" class="flex justify-between">
                            <span class="text-gray-500">Assurance</span>
                            <span class="font-medium">{{ formatCurrency(contract.insurance_monthly) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">TVA ({{ contract.tax_rate }}%)</span>
                            <span class="font-medium">{{ formatCurrency(calculateTax()) }}</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="font-bold">Total TTC mensuel</span>
                            <span class="font-bold text-lg">{{ formatCurrency(contract.total_monthly_amount) }}</span>
                        </div>
                        <div v-if="contract.deposit_amount" class="flex justify-between text-sm">
                            <span class="text-gray-500">Dépôt de garantie</span>
                            <span class="font-medium">{{ formatCurrency(contract.deposit_amount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Actions</h2>
                    <div class="space-y-2">
                        <button
                            v-if="contract.status === 'active'"
                            @click="suspendContract"
                            class="w-full btn btn-warning"
                        >
                            Suspendre le contrat
                        </button>
                        <button
                            v-if="contract.status === 'suspended'"
                            @click="reactivateContract"
                            class="w-full btn btn-success"
                        >
                            Réactiver le contrat
                        </button>
                        <button
                            v-if="contract.status === 'active' || contract.status === 'suspended'"
                            @click="terminateContract"
                            class="w-full btn btn-danger"
                        >
                            Résilier le contrat
                        </button>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="contract.notes" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Notes</h2>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ contract.notes }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    contract: Object,
});

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('fr-FR');
};

const formatCurrency = (amount) => {
    if (!amount) return '0,00 €';
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount);
};

const calculateTax = () => {
    const ht = parseFloat(props.contract.price_monthly_ht) || 0;
    const insurance = parseFloat(props.contract.insurance_monthly) || 0;
    const taxRate = parseFloat(props.contract.tax_rate) || 0;
    return ((ht + insurance) * taxRate) / 100;
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'draft':
            return 'badge bg-secondary';
        case 'active':
            return 'badge bg-success';
        case 'suspended':
            return 'badge bg-warning';
        case 'terminated':
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
};

const getStatusLabel = (status) => {
    const labels = {
        draft: 'Brouillon',
        active: 'Actif',
        suspended: 'Suspendu',
        terminated: 'Résilié',
    };
    return labels[status] || status;
};

const getPaymentMethodLabel = (method) => {
    const labels = {
        sepa: 'Prélèvement SEPA',
        card: 'Carte bancaire',
        transfer: 'Virement bancaire',
        cash: 'Espèces',
        check: 'Chèque',
    };
    return labels[method] || method;
};

const getInvoiceStatusClass = (status) => {
    switch (status) {
        case 'paid':
            return 'badge bg-success';
        case 'pending':
            return 'badge bg-warning';
        case 'overdue':
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
};

const getInvoiceStatusLabel = (status) => {
    const labels = {
        draft: 'Brouillon',
        pending: 'En attente',
        paid: 'Payée',
        overdue: 'En retard',
    };
    return labels[status] || status;
};

const suspendContract = () => {
    if (confirm('Êtes-vous sûr de vouloir suspendre ce contrat ?')) {
        router.post(route('contracts.suspend', props.contract.id));
    }
};

const reactivateContract = () => {
    if (confirm('Êtes-vous sûr de vouloir réactiver ce contrat ?')) {
        router.post(route('contracts.reactivate', props.contract.id));
    }
};

const terminateContract = () => {
    if (confirm('Êtes-vous sûr de vouloir résilier ce contrat ? Cette action est irréversible.')) {
        router.post(route('contracts.terminate', props.contract.id));
    }
};
</script>
