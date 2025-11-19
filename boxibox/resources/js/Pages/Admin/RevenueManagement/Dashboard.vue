<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    sites: Array,
    selected_site_id: Number,
    revenue_gap: Object,
    occupancy_rate: Number,
    recommendations: Array,
});

const selectedSite = ref(props.selected_site_id);
const simulationPercentage = ref(0);
const simulationResult = ref(null);
const loading = ref(false);

const changeSite = () => {
    router.get(route('admin.revenue-management.index'), {
        site_id: selectedSite.value,
    });
};

const updatePrices = () => {
    if (!confirm('Confirmer la mise à jour des prix pour tous les boxes disponibles ?')) {
        return;
    }

    loading.value = true;
    router.post(route('admin.revenue-management.update-prices', selectedSite.value), {}, {
        onSuccess: () => {
            alert('Prix mis à jour avec succès !');
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
};

const runSimulation = async () => {
    try {
        const response = await axios.post(
            route('admin.revenue-management.simulate', selectedSite.value),
            { percentage_change: simulationPercentage.value }
        );
        simulationResult.value = response.data;
    } catch (error) {
        alert('Erreur lors de la simulation');
    }
};

const efficiencyColor = computed(() => {
    if (!props.revenue_gap) return 'gray';
    const eff = props.revenue_gap.efficiency_percentage;
    if (eff >= 90) return 'green';
    if (eff >= 75) return 'yellow';
    return 'red';
});
</script>

<template>
    <AdminLayout title="Revenue Management">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold">Revenue Management</h1>
                    <p class="text-gray-600 mt-1">Optimisation dynamique des prix</p>
                </div>

                <div class="flex items-center gap-4">
                    <select
                        v-model="selectedSite"
                        @change="changeSite"
                        class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option
                            v-for="site in sites"
                            :key="site.id"
                            :value="site.id"
                        >
                            {{ site.name }}
                        </option>
                    </select>

                    <button
                        @click="updatePrices"
                        :disabled="loading"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 font-medium"
                    >
                        {{ loading ? '⏳ Mise à jour...' : '🔄 Mettre à Jour Prix' }}
                    </button>
                </div>
            </div>

            <!-- KPIs Grid -->
            <div class="grid grid-cols-4 gap-6 mb-6">
                <!-- Occupancy Rate -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-gray-500 mb-2">Taux d'Occupation</div>
                    <div class="text-3xl font-bold text-blue-600">
                        {{ occupancy_rate?.toFixed(1) }}%
                    </div>
                    <div class="mt-3 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div
                            class="h-full bg-blue-500 rounded-full transition-all"
                            :style="{ width: occupancy_rate + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Current MRR -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-gray-500 mb-2">MRR Actuel</div>
                    <div class="text-3xl font-bold">
                        {{ revenue_gap?.current_mrr?.toLocaleString() }}€
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ (revenue_gap?.annual_current / 1000)?.toFixed(0) }}k€/an
                    </div>
                </div>

                <!-- Max Potential MRR -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-sm text-gray-500 mb-2">MRR Potentiel Max</div>
                    <div class="text-3xl font-bold text-green-600">
                        {{ revenue_gap?.max_potential_mrr?.toLocaleString() }}€
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ (revenue_gap?.annual_potential / 1000)?.toFixed(0) }}k€/an
                    </div>
                </div>

                <!-- Revenue Gap -->
                <div class="bg-white p-6 rounded-lg shadow border-2"
                    :class="`border-${efficiencyColor}-500`">
                    <div class="text-sm text-gray-500 mb-2">Gap Revenus</div>
                    <div class="text-3xl font-bold text-red-600">
                        {{ revenue_gap?.gap_mrr?.toLocaleString() }}€
                    </div>
                    <div class="text-xs mt-1"
                        :class="`text-${efficiencyColor}-600 font-medium`">
                        Efficacité: {{ revenue_gap?.efficiency_percentage?.toFixed(1) }}%
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold">
                        📊 Recommandations Pricing
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Top {{ recommendations?.length || 0 }} ajustements recommandés
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full" v-if="recommendations && recommendations.length > 0">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left p-4 font-medium text-gray-700">Box</th>
                                <th class="text-right p-4 font-medium text-gray-700">Prix Actuel</th>
                                <th class="text-right p-4 font-medium text-gray-700">Prix Recommandé</th>
                                <th class="text-right p-4 font-medium text-gray-700">Changement</th>
                                <th class="text-left p-4 font-medium text-gray-700">Raison</th>
                                <th class="text-center p-4 font-medium text-gray-700">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="rec in recommendations"
                                :key="rec.box_id"
                                class="border-b hover:bg-gray-50 transition"
                            >
                                <td class="p-4 font-medium">{{ rec.box_number }}</td>
                                <td class="p-4 text-right">{{ rec.current_price }}€</td>
                                <td class="p-4 text-right font-bold text-blue-600">
                                    {{ rec.recommended_price }}€
                                </td>
                                <td
                                    class="p-4 text-right font-bold"
                                    :class="rec.action === 'increase' ? 'text-green-600' : 'text-orange-600'"
                                >
                                    {{ rec.action === 'increase' ? '+' : '' }}{{ rec.percentage_change }}%
                                </td>
                                <td class="p-4 text-sm text-gray-600">{{ rec.reason }}</td>
                                <td class="p-4 text-center">
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-medium"
                                        :class="rec.action === 'increase'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-orange-100 text-orange-800'"
                                    >
                                        {{ rec.action === 'increase' ? '⬆️ Augmenter' : '⬇️ Réduire' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-else class="p-8 text-center text-gray-500">
                        ✅ Aucune recommandation - Les prix sont déjà optimaux !
                    </div>
                </div>
            </div>

            <!-- Price Change Simulator -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold">🎯 Simulateur d'Impact Prix</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Simulez l'impact d'un changement de prix global
                    </p>
                </div>

                <div class="p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <label class="w-48 font-medium">Changement Prix Global:</label>
                        <input
                            v-model.number="simulationPercentage"
                            type="range"
                            min="-30"
                            max="30"
                            step="1"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                        />
                        <span class="w-20 text-right font-bold text-lg">
                            {{ simulationPercentage > 0 ? '+' : '' }}{{ simulationPercentage }}%
                        </span>
                        <button
                            @click="runSimulation"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            Simuler
                        </button>
                    </div>

                    <div v-if="simulationResult" class="grid grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-sm text-gray-500 mb-1">MRR Actuel</div>
                            <div class="text-2xl font-bold">
                                {{ simulationResult.current_mrr.toLocaleString() }}€
                            </div>
                        </div>
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <div class="text-sm text-gray-500 mb-1">MRR Projeté</div>
                            <div class="text-2xl font-bold text-blue-600">
                                {{ simulationResult.projected_mrr.toLocaleString() }}€
                            </div>
                            <div class="text-xs text-gray-600 mt-1">
                                (Demande: {{ simulationResult.estimated_demand_change > 0 ? '+' : '' }}{{ simulationResult.estimated_demand_change }}%)
                            </div>
                        </div>
                        <div class="p-4 bg-green-50 rounded-lg">
                            <div class="text-sm text-gray-500 mb-1">Impact Annuel</div>
                            <div
                                class="text-2xl font-bold"
                                :class="simulationResult.annual_impact > 0 ? 'text-green-600' : 'text-red-600'"
                            >
                                {{ simulationResult.annual_impact > 0 ? '+' : '' }}
                                {{ simulationResult.annual_impact.toLocaleString() }}€
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
