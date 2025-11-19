<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    sites: Array,
    selected_site: Object,
});

const selectedSite = ref(props.selected_site?.id);
const selectedBuilding = ref(null);
const selectedFloor = ref(null);

const goToFloorEditor = () => {
    if (!selectedFloor.value) {
        alert('Veuillez sélectionner un étage');
        return;
    }

    router.visit(route('admin.floor-plan.edit', selectedFloor.value));
};

const site = ref(props.selected_site);

const selectSite = (siteId) => {
    router.visit(route('admin.floor-plan.index', { site_id: siteId }));
};
</script>

<template>
    <AdminLayout title="Gestionnaire de Plans">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- En-tête -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Éditeur de Plans d'Étage</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Organisez visuellement vos boxes sur les plans d'étage
                            </p>
                        </div>
                        <div>
                            <a
                                :href="route('admin.sites.index')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                            >
                                🏢 Gérer les Sites
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sélection du site -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">1. Sélectionnez un Site</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div
                            v-for="s in sites"
                            :key="s.id"
                            @click="selectSite(s.id)"
                            class="p-4 border-2 rounded-lg cursor-pointer transition"
                            :class="{
                                'border-blue-600 bg-blue-50': selectedSite === s.id,
                                'border-gray-300 hover:border-blue-400': selectedSite !== s.id
                            }"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ s.name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ s.city }}</p>
                                    <div class="mt-2 text-xs text-gray-500">
                                        {{ s.buildings?.length || 0 }} bâtiment(s)
                                    </div>
                                </div>
                                <span v-if="selectedSite === s.id" class="text-blue-600 text-2xl">✓</span>
                            </div>
                        </div>

                        <div
                            v-if="sites.length === 0"
                            class="col-span-3 text-center py-8 text-gray-500"
                        >
                            Aucun site disponible.
                            <a :href="route('admin.sites.create')" class="text-blue-600 hover:underline">Créer un site</a>
                        </div>
                    </div>
                </div>

                <!-- Sélection du bâtiment et de l'étage -->
                <div v-if="site" class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Bâtiments -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">2. Sélectionnez un Bâtiment</h3>
                        <div class="space-y-2">
                            <div
                                v-for="building in site.buildings"
                                :key="building.id"
                                @click="selectedBuilding = building.id; selectedFloor = null;"
                                class="p-3 border-2 rounded cursor-pointer transition"
                                :class="{
                                    'border-blue-600 bg-blue-50': selectedBuilding === building.id,
                                    'border-gray-300 hover:border-blue-400': selectedBuilding !== building.id
                                }"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ building.name }}</h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ building.floors?.length || 0 }} étage(s)
                                        </p>
                                    </div>
                                    <span v-if="selectedBuilding === building.id" class="text-blue-600">✓</span>
                                </div>
                            </div>

                            <div v-if="!site.buildings || site.buildings.length === 0" class="text-center py-4 text-gray-500">
                                Aucun bâtiment.
                                <a
                                    :href="route('admin.sites.buildings.create', site.id)"
                                    class="text-blue-600 hover:underline"
                                >
                                    Créer un bâtiment
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Étages -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">3. Sélectionnez un Étage</h3>
                        <div v-if="selectedBuilding" class="space-y-2">
                            <div
                                v-for="floor in site.buildings.find(b => b.id === selectedBuilding)?.floors"
                                :key="floor.id"
                                @click="selectedFloor = floor.id"
                                class="p-3 border-2 rounded cursor-pointer transition"
                                :class="{
                                    'border-blue-600 bg-blue-50': selectedFloor === floor.id,
                                    'border-gray-300 hover:border-blue-400': selectedFloor !== floor.id
                                }"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ floor.name }}</h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Étage {{ floor.floor_number }}
                                        </p>
                                    </div>
                                    <span v-if="selectedFloor === floor.id" class="text-blue-600">✓</span>
                                </div>
                            </div>

                            <div
                                v-if="!site.buildings.find(b => b.id === selectedBuilding)?.floors?.length"
                                class="text-center py-4 text-gray-500"
                            >
                                Aucun étage.
                                <a
                                    :href="route('admin.buildings.floors.create', selectedBuilding)"
                                    class="text-blue-600 hover:underline"
                                >
                                    Créer un étage
                                </a>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-400">
                            Sélectionnez d'abord un bâtiment
                        </div>
                    </div>

                </div>

                <!-- Bouton d'action -->
                <div v-if="selectedFloor" class="mt-6 text-center">
                    <button
                        @click="goToFloorEditor"
                        class="inline-flex items-center px-8 py-3 bg-green-600 text-white text-lg font-semibold rounded-lg hover:bg-green-700 transition shadow-lg"
                    >
                        🎨 Ouvrir l'Éditeur de Plan
                    </button>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
