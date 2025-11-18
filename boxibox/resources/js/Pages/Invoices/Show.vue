<template>
    <AppLayout>
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Facture {{ invoice.invoice_number }}</h1>
                    <p class="text-gray-500 mt-1">
                        <span :class="getStatusBadgeClass(invoice.status)">
                            {{ getStatusLabel(invoice.status) }}
                        </span>
                    </p>
                </div>
                <div class="space-x-2">
                    <Link :href="route('invoices.index')" class="btn btn-secondary">
                        Retour à la liste
                    </Link>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Details -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Détails de la facture</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">N° Facture</label>
                            <p class="text-lg text-gray-900 font-mono">{{ invoice.invoice_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Date de facture</label>
                            <p class="text-lg text-gray-900">{{ formatDate(invoice.invoice_date) }}</p>
                        </div>
                        <div v-if="invoice.due_date">
                            <label class="block text-sm font-medium text-gray-500">Date d'échéance</label>
                            <p class="text-lg text-gray-900">{{ formatDate(invoice.due_date) }}</p>
                        </div>
                        <div v-if="invoice.paid_at">
                            <label class="block text-sm font-medium text-gray-500">Payée le</label>
                            <p class="text-lg text-gray-900">{{ formatDate(invoice.paid_at) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Statut</label>
                            <p>
                                <span :class="getStatusBadgeClass(invoice.status)">
                                    {{ getStatusLabel(invoice.status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Client</h2>
                        <Link
                            v-if="invoice.contract?.customer"
                            :href="route('customers.show', invoice.contract.customer.id)"
                            class="text-indigo-600 hover:text-indigo-900"
                        >
                            Voir la fiche →
                        </Link>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nom</label>
                            <p class="text-lg text-gray-900">
                                {{ invoice.contract?.customer?.type === 'professional' ?
                                    invoice.contract?.customer?.company_name :
                                    `${invoice.contract?.customer?.first_name} ${invoice.contract?.customer?.last_name}` }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="text-lg text-gray-900">{{ invoice.contract?.customer?.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Téléphone</label>
                            <p class="text-lg text-gray-900">{{ invoice.contract?.customer?.phone }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Adresse</label>
                            <p class="text-sm text-gray-900">
                                {{ invoice.contract?.customer?.address }}<br>
                                {{ invoice.contract?.customer?.postal_code }} {{ invoice.contract?.customer?.city }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contract & Box Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Contrat & Box</h2>
                        <Link
                            v-if="invoice.contract"
                            :href="route('contracts.show', invoice.contract.id)"
                            class="text-indigo-600 hover:text-indigo-900"
                        >
                            Voir le contrat →
                        </Link>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">N° Contrat</label>
                            <p class="text-lg text-gray-900 font-mono">{{ invoice.contract?.contract_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Box</label>
                            <p class="text-lg text-gray-900">Box {{ invoice.contract?.box?.number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div v-if="invoice.line_items && invoice.line_items.length > 0" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Détail des lignes</h2>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Prix unit.</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in invoice.line_items" :key="index">
                                <td class="px-4 py-2 text-sm">{{ item.description }}</td>
                                <td class="px-4 py-2 text-sm text-right">{{ item.quantity }}</td>
                                <td class="px-4 py-2 text-sm text-right">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="px-4 py-2 text-sm text-right font-medium">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Payments -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">
                        Paiements ({{ invoice.payments?.length || 0 }})
                    </h2>
                    <div v-if="invoice.payments && invoice.payments.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Méthode</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="payment in invoice.payments" :key="payment.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm">{{ formatDate(payment.payment_date) }}</td>
                                    <td class="px-4 py-2 text-sm font-medium">{{ formatCurrency(payment.amount) }}</td>
                                    <td class="px-4 py-2 text-sm">{{ getPaymentMethodLabel(payment.method) }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span :class="getPaymentStatusClass(payment.status)">
                                            {{ getPaymentStatusLabel(payment.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center text-gray-500 py-4">
                        Aucun paiement enregistré
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Amounts -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Montants</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total HT</span>
                            <span class="font-medium">{{ formatCurrency(invoice.total_ht) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">TVA ({{ invoice.tax_rate }}%)</span>
                            <span class="font-medium">{{ formatCurrency(invoice.tax_amount) }}</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="font-bold">Total TTC</span>
                            <span class="font-bold text-lg">{{ formatCurrency(invoice.total_ttc) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-green-600">Payé</span>
                            <span class="font-medium text-green-600">{{ formatCurrency(invoice.paid_amount) }}</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="font-bold text-red-600">Reste à payer</span>
                            <span class="font-bold text-lg text-red-600">
                                {{ formatCurrency(invoice.total_ttc - (invoice.paid_amount || 0)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="invoice.notes" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Notes</h2>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ invoice.notes }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    invoice: Object,
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

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'draft':
            return 'badge bg-secondary';
        case 'pending':
            return 'badge bg-warning';
        case 'paid':
            return 'badge bg-success';
        case 'overdue':
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
};

const getStatusLabel = (status) => {
    const labels = {
        draft: 'Brouillon',
        pending: 'En attente',
        paid: 'Payée',
        overdue: 'En retard',
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

const getPaymentStatusClass = (status) => {
    switch (status) {
        case 'succeeded':
            return 'badge bg-success';
        case 'pending':
            return 'badge bg-warning';
        case 'failed':
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
};

const getPaymentStatusLabel = (status) => {
    const labels = {
        succeeded: 'Réussi',
        pending: 'En attente',
        failed: 'Échoué',
    };
    return labels[status] || status;
};
</script>
