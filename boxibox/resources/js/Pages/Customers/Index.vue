<template>
    <AppLayout>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Clients</h1>
            <Link :href="route('customers.create')" class="btn btn-primary">
                Nouveau client
            </Link>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="form-control"
                        placeholder="Rechercher un client..."
                        @input="search"
                    />
                </div>
                <div>
                    <select v-model="filters.type" class="form-select" @change="search">
                        <option value="">Tous les types</option>
                        <option value="individual">Particulier</option>
                        <option value="professional">Professionnel</option>
                    </select>
                </div>
                <div>
                    <select v-model="filters.status" class="form-select" @change="search">
                        <option value="">Tous les statuts</option>
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            N° Client
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nom / Raison sociale
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contrats actifs
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
                    <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ customer.customer_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ customer.type === 'professional' ? customer.company_name : `${customer.first_name} ${customer.last_name}` }}
                            </div>
                            <div v-if="customer.type === 'professional' && customer.first_name" class="text-sm text-gray-500">
                                Contact: {{ customer.first_name }} {{ customer.last_name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="customer.type === 'professional' ? 'badge bg-primary' : 'badge bg-success'">
                                {{ customer.type === 'professional' ? 'Professionnel' : 'Particulier' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ customer.email }}</div>
                            <div class="text-sm text-gray-500">{{ customer.phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ customer.active_contracts_count || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="customer.status === 'active' ? 'badge bg-success' : 'badge bg-secondary'">
                                {{ customer.status === 'active' ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link :href="route('customers.show', customer.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                Voir
                            </Link>
                            <Link :href="route('customers.edit', customer.id)" class="text-blue-600 hover:text-blue-900 mr-3">
                                Éditer
                            </Link>
                            <button @click="deleteCustomer(customer.id)" class="text-red-600 hover:text-red-900">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="customers.links && customers.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Affichage de {{ customers.from }} à {{ customers.to }} sur {{ customers.total }} résultats
                    </div>
                    <div class="flex space-x-1">
                        <Link
                            v-for="(link, index) in customers.links"
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
    customers: Object,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    status: props.filters?.status || '',
});

const search = debounce(() => {
    router.get(route('customers.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const deleteCustomer = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
        router.delete(route('customers.destroy', id));
    }
};
</script>
