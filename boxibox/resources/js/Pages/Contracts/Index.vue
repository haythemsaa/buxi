<template>
    <AppLayout>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Contrats</h1>
            <Link :href="route('contracts.create')" class="btn btn-primary">
                Nouveau contrat
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="form-control"
                        placeholder="Rechercher..."
                        @input="search"
                    />
                </div>
                <div>
                    <select v-model="filters.status" class="form-select" @change="search">
                        <option value="">Tous les statuts</option>
                        <option value="draft">Brouillon</option>
                        <option value="active">Actif</option>
                        <option value="suspended">Suspendu</option>
                        <option value="terminated">Résilié</option>
                    </select>
                </div>
                <div>
                    <select v-model="filters.payment_method" class="form-select" @change="search">
                        <option value="">Tous les modes de paiement</option>
                        <option value="sepa">SEPA</option>
                        <option value="card">Carte bancaire</option>
                        <option value="transfer">Virement</option>
                        <option value="cash">Espèces</option>
                        <option value="check">Chèque</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            N° Contrat
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Client
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Box
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dates
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Montant
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="contract in contracts.data" :key="contract.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ contract.contract_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ contract.customer?.company_name || `${contract.customer?.first_name} ${contract.customer?.last_name}` }}
                            </div>
                            <div class="text-sm text-gray-500">{{ contract.customer?.email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">Box {{ contract.box?.number }}</div>
                            <div class="text-sm text-gray-500">{{ Math.round(contract.box?.volume) }}m³</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">Début: {{ formatDate(contract.start_date) }}</div>
                            <div v-if="contract.end_date" class="text-sm text-gray-500">Fin: {{ formatDate(contract.end_date) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ formatCurrency(contract.total_monthly_amount) }}/mois
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatusBadgeClass(contract.status)">
                                {{ getStatusLabel(contract.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link :href="route('contracts.show', contract.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                Voir
                            </Link>
                            <Link :href="route('contracts.edit', contract.id)" class="text-blue-600 hover:text-blue-900 mr-3">
                                Éditer
                            </Link>
                            <button
                                v-if="contract.status === 'active'"
                                @click="suspendContract(contract.id)"
                                class="text-orange-600 hover:text-orange-900 mr-3"
                            >
                                Suspendre
                            </button>
                            <button
                                v-if="contract.status === 'active' || contract.status === 'suspended'"
                                @click="terminateContract(contract.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Résilier
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="contracts.links && contracts.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Affichage de {{ contracts.from }} à {{ contracts.to }} sur {{ contracts.total }} résultats
                    </div>
                    <div class="flex space-x-1">
                        <Link
                            v-for="(link, index) in contracts.links"
                            :key="index"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm rounded',
                                link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { debounce } from 'lodash';

const props = defineProps({
    contracts: Object,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    payment_method: props.filters?.payment_method || '',
});

const search = debounce(() => {
    router.get(route('contracts.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

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

const suspendContract = (id) => {
    if (confirm('Êtes-vous sûr de vouloir suspendre ce contrat ?')) {
        router.post(route('contracts.suspend', id));
    }
};

const terminateContract = (id) => {
    if (confirm('Êtes-vous sûr de vouloir résilier ce contrat ? Cette action est irréversible.')) {
        router.post(route('contracts.terminate', id));
    }
};
</script>
