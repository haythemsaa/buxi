<template>
    <AppLayout>
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ customer.type === 'professional' ? customer.company_name : `${customer.first_name} ${customer.last_name}` }}
                </h1>
                <div class="space-x-2">
                    <Link :href="route('customers.edit', customer.id)" class="btn btn-primary">
                        Modifier
                    </Link>
                    <Link :href="route('customers.index')" class="btn btn-secondary">
                        Retour à la liste
                    </Link>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Informations client</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">N° Client</label>
                    <p class="text-lg text-gray-900 font-mono">{{ customer.customer_number }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Type</label>
                    <span :class="customer.type === 'professional' ? 'badge bg-primary' : 'badge bg-success'">
                        {{ customer.type === 'professional' ? 'Professionnel' : 'Particulier' }}
                    </span>
                </div>

                <div v-if="customer.type === 'professional' && customer.siret">
                    <label class="block text-sm font-medium text-gray-500">SIRET</label>
                    <p class="text-lg text-gray-900">{{ customer.siret }}</p>
                </div>
                <div v-if="customer.type === 'professional' && customer.vat_number">
                    <label class="block text-sm font-medium text-gray-500">N° TVA</label>
                    <p class="text-lg text-gray-900">{{ customer.vat_number }}</p>
                </div>

                <div v-if="customer.type === 'professional' && customer.first_name">
                    <label class="block text-sm font-medium text-gray-500">Personne de contact</label>
                    <p class="text-lg text-gray-900">{{ customer.first_name }} {{ customer.last_name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500">Email</label>
                    <p class="text-lg text-gray-900">{{ customer.email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Téléphone</label>
                    <p class="text-lg text-gray-900">{{ customer.phone }}</p>
                </div>
                <div v-if="customer.phone_secondary">
                    <label class="block text-sm font-medium text-gray-500">Téléphone secondaire</label>
                    <p class="text-lg text-gray-900">{{ customer.phone_secondary }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500">Adresse</label>
                    <p class="text-lg text-gray-900">
                        {{ customer.address }}<br>
                        {{ customer.postal_code }} {{ customer.city }}
                        <span v-if="customer.country">, {{ customer.country }}</span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500">Statut</label>
                    <span :class="customer.status === 'active' ? 'badge bg-success' : 'badge bg-secondary'">
                        {{ customer.status === 'active' ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Contracts -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Contrats ({{ customer.contracts?.length || 0 }})</h2>
                <Link :href="route('contracts.create', { customer_id: customer.id })" class="btn btn-sm btn-primary">
                    Nouveau contrat
                </Link>
            </div>

            <div v-if="customer.contracts && customer.contracts.length > 0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Contrat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Box</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date début</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="contract in customer.contracts" :key="contract.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ contract.contract_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">Box {{ contract.box?.number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatDate(contract.start_date) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                {{ formatCurrency(contract.total_monthly_amount) }}/mois
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusBadgeClass(contract.status)">
                                    {{ getStatusLabel(contract.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <Link :href="route('contracts.show', contract.id)" class="text-indigo-600 hover:text-indigo-900">
                                    Voir
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-center text-gray-500 py-8">
                Aucun contrat pour ce client
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    customer: Object,
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
</script>
