<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Réservez votre box</h1>
            <p class="text-gray-500 mt-1">Trouvez le box idéal pour vos besoins</p>
        </div>

        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Rechercher un box</h2>
            <form @submit.prevent="searchBoxes" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site</label>
                    <select v-model="searchForm.site_id" class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Tous les sites</option>
                        <option v-for="site in sites" :key="site.id" :value="site.id">
                            {{ site.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Volume min. (m³)</label>
                    <input v-model="searchForm.min_volume" type="number" step="0.1" placeholder="Ex: 2.5"
                           class="w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Volume max. (m³)</label>
                    <input v-model="searchForm.max_volume" type="number" step="0.1" placeholder="Ex: 10"
                           class="w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durée (mois)</label>
                    <input v-model="searchForm.duration_months" type="number" min="1" max="24" placeholder="1"
                           class="w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div class="flex items-center">
                    <input v-model="searchForm.climate_controlled" type="checkbox" id="climate"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm">
                    <label for="climate" class="ml-2 text-sm text-gray-700">Climatisé uniquement</label>
                </div>

                <div class="flex items-center">
                    <input v-model="searchForm.ground_floor" type="checkbox" id="ground"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm">
                    <label for="ground" class="ml-2 text-sm text-gray-700">Rez-de-chaussée</label>
                </div>

                <div class="flex items-center">
                    <input v-model="searchForm.vehicle_access" type="checkbox" id="vehicle"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm">
                    <label for="vehicle" class="ml-2 text-sm text-gray-700">Accès véhicule</label>
                </div>

                <div class="flex items-center">
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Rechercher
                    </button>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div v-if="boxes && boxes.length > 0">
            <div class="mb-4 flex justify-between items-center">
                <p class="text-gray-600">{{ boxes.length }} box(es) disponible(s)</p>
                <button @click="compareMode = !compareMode" class="text-indigo-600 hover:text-indigo-900">
                    {{ compareMode ? 'Annuler comparaison' : 'Comparer les boxes' }}
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div v-for="box in boxes" :key="box.id"
                     class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
                     :class="{ 'ring-2 ring-indigo-500': selectedBoxes.includes(box.id) }">

                    <!-- Box Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold">{{ box.number }}</h3>
                            <p class="text-sm text-gray-500">{{ box.site_name }}</p>
                        </div>
                        <span class="badge bg-success">Disponible</span>
                    </div>

                    <!-- Box Details -->
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Dimensions</span>
                            <span class="font-medium">{{ box.length }}×{{ box.width }}×{{ box.height }} cm</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Volume</span>
                            <span class="font-medium">{{ box.volume.toFixed(2) }} m³</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Catégorie</span>
                            <span class="font-medium">{{ getSizeCategoryLabel(box.size_category) }}</span>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span v-if="box.climate_controlled" class="badge bg-info">Climatisé</span>
                        <span v-if="box.ground_floor" class="badge bg-secondary">RDC</span>
                        <span v-if="box.vehicle_access" class="badge bg-secondary">Accès véhicule</span>
                    </div>

                    <!-- Pricing -->
                    <div class="border-t pt-4 mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Prix mensuel</span>
                            <div class="text-right">
                                <div v-if="box.pricing.discount_amount > 0" class="text-xs text-gray-500 line-through">
                                    {{ formatCurrency(box.pricing.monthly_price_ht + box.pricing.discount_amount) }}
                                </div>
                                <div class="text-lg font-bold text-indigo-600">
                                    {{ formatCurrency(box.pricing.total_monthly_ttc) }}
                                </div>
                            </div>
                        </div>
                        <div v-if="box.pricing.promotion" class="text-xs text-green-600">
                            {{ box.pricing.promotion.name }} (-{{ formatCurrency(box.pricing.discount_amount) }})
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button v-if="compareMode"
                                @click="toggleSelection(box.id)"
                                class="flex-1 px-4 py-2 rounded-md border"
                                :class="selectedBoxes.includes(box.id) ?
                                    'bg-indigo-600 text-white border-indigo-600' :
                                    'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                            {{ selectedBoxes.includes(box.id) ? 'Sélectionné' : 'Sélectionner' }}
                        </button>
                        <Link v-else
                              :href="route('reservations.create', { box_id: box.id, duration: searchForm.duration_months })"
                              class="flex-1 text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Réserver
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Compare Button -->
            <div v-if="compareMode && selectedBoxes.length >= 2" class="text-center">
                <button @click="compareBoxes"
                        class="bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700">
                    Comparer {{ selectedBoxes.length }} boxes
                </button>
            </div>
        </div>

        <!-- No Results -->
        <div v-else-if="searched" class="bg-white rounded-lg shadow p-12 text-center">
            <p class="text-gray-500 text-lg">Aucun box disponible avec ces critères</p>
            <button @click="resetSearch" class="mt-4 text-indigo-600 hover:text-indigo-900">
                Réinitialiser la recherche
            </button>
        </div>

        <!-- Comparison Modal -->
        <div v-if="showComparison" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click.self="showComparison = false">
            <div class="bg-white rounded-lg p-6 max-w-6xl w-full mx-4 max-h-[90vh] overflow-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Comparaison de boxes</h2>
                    <button @click="showComparison = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div v-if="comparisonData">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Critère</th>
                                    <th v-for="box in comparisonData.boxes" :key="box.id"
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ box.number }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-4 py-3 font-medium">Prix mensuel TTC</td>
                                    <td v-for="box in comparisonData.boxes" :key="'price-' + box.id"
                                        class="px-4 py-3 text-center font-bold text-indigo-600">
                                        {{ formatCurrency(box.pricing.total_monthly_ttc) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-medium">Volume</td>
                                    <td v-for="box in comparisonData.boxes" :key="'vol-' + box.id" class="px-4 py-3 text-center">
                                        {{ box.volume.toFixed(2) }} m³
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-medium">Dimensions</td>
                                    <td v-for="box in comparisonData.boxes" :key="'dim-' + box.id" class="px-4 py-3 text-center">
                                        {{ box.length }}×{{ box.width }}×{{ box.height }} cm
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-medium">Score</td>
                                    <td v-for="box in comparisonData.boxes" :key="'score-' + box.id" class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                              :class="getScoreClass(box.score)">
                                            {{ box.score }}/100
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        <button @click="showComparison = false" class="px-4 py-2 border rounded-md hover:bg-gray-50">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    sites: Array,
    initialBoxes: Array,
});

const searched = ref(false);
const boxes = ref(props.initialBoxes || []);
const compareMode = ref(false);
const selectedBoxes = ref([]);
const showComparison = ref(false);
const comparisonData = ref(null);

const searchForm = reactive({
    site_id: '',
    min_volume: '',
    max_volume: '',
    duration_months: 1,
    climate_controlled: false,
    ground_floor: false,
    vehicle_access: false,
});

const searchBoxes = async () => {
    try {
        const response = await axios.post(route('reservations.search'), searchForm);
        boxes.value = response.data.boxes;
        searched.value = true;
    } catch (error) {
        console.error('Search error:', error);
    }
};

const resetSearch = () => {
    Object.assign(searchForm, {
        site_id: '',
        min_volume: '',
        max_volume: '',
        duration_months: 1,
        climate_controlled: false,
        ground_floor: false,
        vehicle_access: false,
    });
    boxes.value = [];
    searched.value = false;
};

const toggleSelection = (boxId) => {
    const index = selectedBoxes.value.indexOf(boxId);
    if (index > -1) {
        selectedBoxes.value.splice(index, 1);
    } else {
        if (selectedBoxes.value.length < 5) {
            selectedBoxes.value.push(boxId);
        }
    }
};

const compareBoxes = async () => {
    try {
        const response = await axios.post(route('reservations.compare'), {
            box_ids: selectedBoxes.value,
            duration_months: searchForm.duration_months || 1,
        });
        comparisonData.value = response.data;
        showComparison.value = true;
    } catch (error) {
        console.error('Comparison error:', error);
    }
};

const formatCurrency = (amount) => {
    if (!amount) return '0,00 €';
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount);
};

const getSizeCategoryLabel = (category) => {
    const labels = {
        mini: 'Mini',
        small: 'Petit',
        medium: 'Moyen',
        large: 'Grand',
        xl: 'Très grand',
    };
    return labels[category] || category;
};

const getScoreClass = (score) => {
    if (score >= 80) return 'bg-green-100 text-green-800';
    if (score >= 60) return 'bg-yellow-100 text-yellow-800';
    return 'bg-gray-100 text-gray-800';
};
</script>
