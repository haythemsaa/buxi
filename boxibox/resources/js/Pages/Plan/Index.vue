<template>
    <AppLayout>
        <!-- Header -->
        <div class="mb-4">
            <h1 class="text-3xl font-bold text-gray-900">Plans - État des boxes</h1>
        </div>

        <!-- Site Selector -->
        <div class="mb-4" v-if="sites.length > 1">
            <select
                v-model="selectedSiteId"
                @change="changeSite"
                class="form-select rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option v-for="site in sites" :key="site.id" :value="site.id">
                    {{ site.name }}
                </option>
            </select>
        </div>

        <!-- Statistics Bar -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex items-center gap-4 text-lg">
                <div class="flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="font-semibold text-cyan-600">
                        PLAN - NB BOX : {{ stats.totalBoxes }} - OCCUPÉ : {{ stats.occupiedBoxes }} - LIBRE : {{ stats.availableBoxes }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-12 h-8 bg-blue-500 border border-gray-300 rounded"></div>
                    <span class="text-sm font-medium">Occupé</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-12 h-8 bg-green-400 border border-gray-300 rounded"></div>
                    <span class="text-sm font-medium">Libre</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-12 h-8 bg-yellow-300 border border-gray-300 rounded"></div>
                    <span class="text-sm font-medium">Réservé</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-12 h-8 bg-gray-300 border border-gray-300 rounded"></div>
                    <span class="text-sm font-medium">Maintenance</span>
                </div>
            </div>
        </div>

        <!-- Buildings and Floors -->
        <div v-if="selectedSite" class="space-y-8">
            <div v-for="building in buildings" :key="building.id" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    {{ building.name }}
                    <span class="text-sm font-normal text-gray-500 ml-2">
                        ({{ building.type === 'interior' ? 'Intérieur' : 'Extérieur' }}{{ building.climate_controlled ? ' - Climatisé' : '' }})
                    </span>
                </h2>

                <!-- Floors -->
                <div v-for="floor in building.floors" :key="floor.id" class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">
                        {{ floor.name }}
                    </h3>

                    <!-- Boxes Grid -->
                    <div class="plan-grid">
                        <div
                            v-for="box in floor.boxes"
                            :key="box.id"
                            @click="showBoxDetails(box)"
                            :class="getBoxClass(box)"
                            class="box-item"
                            :title="`Box ${box.number} - ${box.status}`"
                        >
                            <div class="box-number">{{ box.number }}</div>
                            <div class="box-size">{{ Math.round(box.volume) }}m3</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-gray-500">Aucun site disponible</p>
        </div>

        <!-- Box Details Modal -->
        <div
            class="modal fade"
            id="boxDetailsModal"
            tabindex="-1"
            aria-labelledby="boxDetailsModalLabel"
            aria-hidden="true"
            ref="boxModal"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" v-if="selectedBox">
                    <div class="modal-header bg-blue-600 text-white">
                        <h5 class="modal-title" id="boxDetailsModalLabel">
                            Box {{ selectedBox.number }}
                        </h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <div class="space-y-3">
                            <div>
                                <strong>Type :</strong>
                                {{ Math.round(selectedBox.volume) }}m3
                                ({{ selectedBox.surface }} m2 - {{ Math.round(selectedBox.volume) }} m3)
                            </div>

                            <div>
                                <strong>État :</strong>
                                <span :class="getStatusBadgeClass(selectedBox.status)">
                                    {{ getStatusLabel(selectedBox.status) }}
                                </span>
                            </div>

                            <div v-if="selectedBox.current_contract">
                                <strong>Contrat :</strong>
                                {{ selectedBox.current_contract.contract_number }}
                            </div>

                            <div v-if="selectedBox.current_contract">
                                <strong>Début le :</strong>
                                {{ formatDate(selectedBox.current_contract.start_date) }}
                            </div>

                            <div v-if="selectedBox.current_contract && selectedBox.current_contract.customer">
                                <strong>Client :</strong>
                                {{ getCustomerName(selectedBox.current_contract.customer) }}
                            </div>

                            <div>
                                <strong>Prix mensuel :</strong>
                                {{ formatCurrency(selectedBox.current_price_monthly) }}
                            </div>

                            <div v-if="selectedBox.features && selectedBox.features.length > 0">
                                <strong>Équipements :</strong>
                                {{ selectedBox.features.join(', ') }}
                            </div>

                            <div class="mt-4 pt-3 border-t">
                                <strong>Caractéristiques :</strong>
                                <ul class="list-unstyled mt-2">
                                    <li v-if="selectedBox.climate_controlled">✓ Climatisé</li>
                                    <li v-if="selectedBox.ground_floor">✓ Rez-de-chaussée</li>
                                    <li v-if="selectedBox.vehicle_access">✓ Accès véhicule</li>
                                    <li v-if="selectedBox.has_electricity">✓ Électricité</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Fermer
                        </button>
                        <Link
                            v-if="selectedBox.status === 'available'"
                            :href="route('contracts.create', { box_id: selectedBox.id })"
                            class="btn btn-primary"
                        >
                            Créer un contrat
                        </Link>
                        <Link
                            v-else-if="selectedBox.current_contract"
                            :href="route('contracts.show', selectedBox.current_contract.id)"
                            class="btn btn-primary"
                        >
                            Voir le contrat
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Modal } from 'bootstrap';

const props = defineProps({
    sites: Array,
    selectedSite: Object,
    buildings: Array,
    stats: Object,
});

const selectedSiteId = ref(props.selectedSite ? props.selectedSite.id : null);
const selectedBox = ref(null);
const boxModal = ref(null);
let modalInstance = null;

onMounted(() => {
    if (boxModal.value) {
        modalInstance = new Modal(boxModal.value);
    }
});

const changeSite = () => {
    router.get(route('plan.index'), { site_id: selectedSiteId.value });
};

const showBoxDetails = (box) => {
    selectedBox.value = box;
    if (modalInstance) {
        modalInstance.show();
    }
};

const getBoxClass = (box) => {
    const baseClass = 'cursor-pointer hover:opacity-80 transition-opacity border border-gray-300 rounded flex flex-col items-center justify-center text-xs font-semibold';

    switch (box.status) {
        case 'occupied':
            return `${baseClass} bg-blue-500 text-white`;
        case 'available':
            return `${baseClass} bg-green-400 text-gray-800`;
        case 'reserved':
            return `${baseClass} bg-yellow-300 text-gray-800`;
        case 'maintenance':
            return `${baseClass} bg-gray-300 text-gray-600`;
        default:
            return `${baseClass} bg-gray-200 text-gray-600`;
    }
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'occupied':
            return 'badge bg-primary';
        case 'available':
            return 'badge bg-success';
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
        occupied: 'Occupé',
        available: 'Libre',
        reserved: 'Réservé',
        maintenance: 'Maintenance',
    };
    return labels[status] || status;
};

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

const getCustomerName = (customer) => {
    if (!customer) return '';
    if (customer.type === 'professional' && customer.company_name) {
        return customer.company_name;
    }
    return `${customer.first_name} ${customer.last_name}`;
};
</script>

<style scoped>
.plan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 8px;
    margin-bottom: 1rem;
}

.box-item {
    height: 80px;
    padding: 4px;
}

.box-number {
    font-size: 0.75rem;
    font-weight: 700;
    margin-bottom: 2px;
}

.box-size {
    font-size: 0.65rem;
    font-weight: 500;
}
</style>
