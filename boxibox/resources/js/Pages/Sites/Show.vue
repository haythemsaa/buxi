<template>
    <AppLayout>
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">{{ site.name }}</h1>
                <div class="space-x-2">
                    <Link :href="route('sites.edit', site.id)" class="btn btn-primary">
                        Modifier
                    </Link>
                    <Link :href="route('sites.index')" class="btn btn-secondary">
                        Retour à la liste
                    </Link>
                </div>
            </div>
        </div>

        <!-- Site Information -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Informations générales</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nom</label>
                    <p class="text-lg text-gray-900">{{ site.name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Téléphone</label>
                    <p class="text-lg text-gray-900">{{ site.phone || 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Email</label>
                    <p class="text-lg text-gray-900">{{ site.email || 'N/A' }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500">Adresse</label>
                    <p class="text-lg text-gray-900">
                        {{ site.address }}<br>
                        {{ site.postal_code }} {{ site.city }}
                        <span v-if="site.country">, {{ site.country }}</span>
                    </p>
                </div>
                <div v-if="site.gps_latitude && site.gps_longitude">
                    <label class="block text-sm font-medium text-gray-500">Coordonnées GPS</label>
                    <p class="text-lg text-gray-900">{{ site.gps_latitude }}, {{ site.gps_longitude }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Bâtiments</h3>
                <p class="text-3xl font-bold text-indigo-600">{{ site.buildings?.length || 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Boxes totaux</h3>
                <p class="text-3xl font-bold text-indigo-600">{{ getTotalBoxes() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Taux d'occupation</h3>
                <p class="text-3xl font-bold text-indigo-600">{{ getOccupancyRate() }}%</p>
            </div>
        </div>

        <!-- Buildings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Bâtiments</h2>
            <div v-if="site.buildings && site.buildings.length > 0" class="space-y-4">
                <div v-for="building in site.buildings" :key="building.id" class="border rounded-lg p-4">
                    <h3 class="text-lg font-semibold mb-2">{{ building.name }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Type:</span>
                            <span class="ml-2 font-medium">{{ building.type === 'interior' ? 'Intérieur' : 'Extérieur' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Climatisé:</span>
                            <span class="ml-2 font-medium">{{ building.climate_controlled ? 'Oui' : 'Non' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Étages:</span>
                            <span class="ml-2 font-medium">{{ building.floors?.length || 0 }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Boxes:</span>
                            <span class="ml-2 font-medium">{{ getBuildingBoxCount(building) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center text-gray-500 py-8">
                Aucun bâtiment pour ce site
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    site: Object,
});

const getTotalBoxes = () => {
    if (!props.site.buildings) return 0;
    return props.site.buildings.reduce((total, building) => {
        if (!building.floors) return total;
        return total + building.floors.reduce((floorTotal, floor) => {
            return floorTotal + (floor.boxes?.length || 0);
        }, 0);
    }, 0);
};

const getOccupancyRate = () => {
    const total = getTotalBoxes();
    if (total === 0) return 0;

    let occupied = 0;
    if (props.site.buildings) {
        props.site.buildings.forEach(building => {
            if (building.floors) {
                building.floors.forEach(floor => {
                    if (floor.boxes) {
                        occupied += floor.boxes.filter(box => box.status === 'occupied').length;
                    }
                });
            }
        });
    }

    return Math.round((occupied / total) * 100);
};

const getBuildingBoxCount = (building) => {
    if (!building.floors) return 0;
    return building.floors.reduce((total, floor) => {
        return total + (floor.boxes?.length || 0);
    }, 0);
};
</script>
