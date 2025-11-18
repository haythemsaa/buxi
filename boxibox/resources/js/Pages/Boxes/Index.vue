<template>
    <AppLayout>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Boxes</h1>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="form-control"
                        placeholder="Rechercher un box..."
                        @input="search"
                    />
                </div>
                <div>
                    <select v-model="filters.status" class="form-select" @change="search">
                        <option value="">Tous les statuts</option>
                        <option value="available">Disponible</option>
                        <option value="occupied">Occupé</option>
                        <option value="reserved">Réservé</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div>
                    <select v-model="filters.site_id" class="form-select" @change="search">
                        <option value="">Tous les sites</option>
                        <option v-for="site in sites" :key="site.id" :value="site.id">
                            {{ site.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            N° Box
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Site / Bâtiment
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Étage
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dimensions
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Prix mensuel
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
                    <tr v-for="box in boxes.data" :key="box.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ box.number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ box.floor?.building?.site?.name }}</div>
                            <div class="text-sm text-gray-500">{{ box.floor?.building?.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ box.floor?.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ box.surface }}m² - {{ Math.round(box.volume) }}m³</div>
                            <div class="text-sm text-gray-500">{{ box.length }}×{{ box.width }}×{{ box.height }}m</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ formatCurrency(box.current_price_monthly) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatusBadgeClass(box.status)">
                                {{ getStatusLabel(box.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link
                                v-if="box.status === 'available'"
                                :href="route('contracts.create', { box_id: box.id })"
                                class="text-green-600 hover:text-green-900"
                            >
                                Créer contrat
                            </Link>
                            <Link
                                v-else-if="box.current_contract"
                                :href="route('contracts.show', box.current_contract.id)"
                                class="text-indigo-600 hover:text-indigo-900"
                            >
                                Voir contrat
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="boxes.links && boxes.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Affichage de {{ boxes.from }} à {{ boxes.to }} sur {{ boxes.total }} résultats
                    </div>
                    <div class="flex space-x-1">
                        <Link
                            v-for="(link, index) in boxes.links"
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
    boxes: Object,
    sites: Array,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    site_id: props.filters?.site_id || '',
});

const search = debounce(() => {
    router.get(route('boxes.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const formatCurrency = (amount) => {
    if (!amount) return '0,00 €';
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount);
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'available':
            return 'badge bg-success';
        case 'occupied':
            return 'badge bg-primary';
        case 'reserved':
            return 'badge bg-warning';
        case 'maintenance':
            return 'badge bg-secondary';
        default:
            return 'badge bg-secondary';
    }
};

const getStatusLabel = (status) => {
    const labels = {
        available: 'Disponible',
        occupied: 'Occupé',
        reserved: 'Réservé',
        maintenance: 'Maintenance',
    };
    return labels[status] || status;
};
</script>
