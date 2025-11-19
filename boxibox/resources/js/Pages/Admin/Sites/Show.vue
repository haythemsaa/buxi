<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    site: Object,
    stats: Object,
});

const deleteSite = () => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le site "${props.site.name}" ?`)) {
        router.delete(route('admin.sites.destroy', props.site.id));
    }
};
</script>

<template>
    <AdminLayout :title="site.name">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- En-tête avec actions -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900">{{ site.name }}</h2>
                            <p class="mt-1 text-gray-600">{{ site.address }}, {{ site.postal_code }} {{ site.city }}</p>
                            <div class="mt-2 flex items-center gap-4">
                                <span
                                    class="px-3 py-1 text-sm font-semibold rounded"
                                    :class="{
                                        'bg-green-100 text-green-800': site.status === 'active',
                                        'bg-red-100 text-red-800': site.status === 'inactive',
                                        'bg-yellow-100 text-yellow-800': site.status === 'maintenance'
                                    }"
                                >
                                    {{ site.status === 'active' ? 'Actif' : site.status === 'inactive' ? 'Inactif' : 'Maintenance' }}
                                </span>
                                <span v-if="site.phone" class="text-sm text-gray-600">📞 {{ site.phone }}</span>
                                <span v-if="site.email" class="text-sm text-gray-600">✉️ {{ site.email }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('admin.floor-plan.index', { site_id: site.id })"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                            >
                                🎨 Ouvrir les Plans
                            </Link>
                            <Link
                                :href="route('admin.sites.edit', site.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                            >
                                ✏️ Modifier
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="text-sm text-gray-600 mb-1">Bâtiments</div>
                        <div class="text-3xl font-bold text-blue-600">{{ stats.total_buildings }}</div>
                    </div>
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="text-sm text-gray-600 mb-1">Étages</div>
                        <div class="text-3xl font-bold text-purple-600">{{ stats.total_floors }}</div>
                    </div>
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="text-sm text-gray-600 mb-1">Total Boxes</div>
                        <div class="text-3xl font-bold text-gray-800">{{ stats.total_boxes }}</div>
                    </div>
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="text-sm text-gray-600 mb-1">Taux d'Occupation</div>
                        <div class="text-3xl font-bold text-green-600">
                            {{ stats.total_boxes > 0 ? Math.round((stats.occupied_boxes / stats.total_boxes) * 100) : 0 }}%
                        </div>
                    </div>
                </div>

                <!-- Liste des bâtiments -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Bâtiments</h3>
                        <Link
                            :href="route('admin.sites.buildings.create', site.id)"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm"
                        >
                            ➕ Nouveau Bâtiment
                        </Link>
                    </div>

                    <div v-if="site.buildings && site.buildings.length > 0" class="space-y-4">
                        <div
                            v-for="building in site.buildings"
                            :key="building.id"
                            class="border-2 border-gray-200 rounded-lg p-4 hover:border-blue-400 transition"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-4 h-4 rounded"
                                            :style="{ backgroundColor: building.plan_color || '#3B82F6' }"
                                        ></div>
                                        <h4 class="text-lg font-semibold text-gray-900">{{ building.name }}</h4>
                                    </div>
                                    <p v-if="building.description" class="text-sm text-gray-600 mt-1">{{ building.description }}</p>
                                    <div class="mt-2 text-sm text-gray-500">
                                        {{ building.floors_count || 0 }} étage(s)
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="route('admin.sites.buildings.show', [site.id, building.id])"
                                        class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition"
                                    >
                                        Voir Détails
                                    </Link>
                                    <Link
                                        :href="route('admin.sites.buildings.edit', [site.id, building.id])"
                                        class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition"
                                    >
                                        Modifier
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12">
                        <div class="text-6xl mb-4">🏢</div>
                        <p class="text-gray-600 mb-4">Aucun bâtiment pour ce site</p>
                        <Link
                            :href="route('admin.sites.buildings.create', site.id)"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                        >
                            ➕ Créer le Premier Bâtiment
                        </Link>
                    </div>
                </div>

                <!-- Actions en bas -->
                <div class="mt-6 flex items-center justify-between">
                    <Link
                        :href="route('admin.sites.index')"
                        class="text-sm text-gray-600 hover:text-gray-900"
                    >
                        ← Retour à la liste des sites
                    </Link>

                    <button
                        v-if="site.buildings && site.buildings.length === 0"
                        @click="deleteSite"
                        class="text-sm text-red-600 hover:text-red-800"
                    >
                        🗑️ Supprimer le site
                    </button>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
