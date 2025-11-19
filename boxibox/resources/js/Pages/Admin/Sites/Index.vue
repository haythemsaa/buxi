<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    sites: Array,
});
</script>

<template>
    <AdminLayout title="Gestion des Sites">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- En-tête avec bouton créer -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Mes Sites de Self-Storage</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Gérez tous vos sites, bâtiments, étages et boxes
                            </p>
                        </div>
                        <Link
                            :href="route('admin.sites.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                        >
                            ➕ Nouveau Site
                        </Link>
                    </div>
                </div>

                <!-- Liste des sites -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="site in sites"
                        :key="site.id"
                        class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition"
                    >
                        <!-- En-tête de la carte -->
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-semibold text-gray-900">{{ site.name }}</h3>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded"
                                    :class="{
                                        'bg-green-100 text-green-800': site.status === 'active',
                                        'bg-red-100 text-red-800': site.status === 'inactive',
                                        'bg-yellow-100 text-yellow-800': site.status === 'maintenance'
                                    }"
                                >
                                    {{ site.status }}
                                </span>
                            </div>

                            <!-- Adresse -->
                            <div class="text-sm text-gray-600 mb-4">
                                <div>{{ site.address }}</div>
                                <div>{{ site.postal_code }} {{ site.city }}</div>
                                <div v-if="site.phone" class="mt-1">📞 {{ site.phone }}</div>
                                <div v-if="site.email" class="mt-1">✉️ {{ site.email }}</div>
                            </div>

                            <!-- Statistiques -->
                            <div class="grid grid-cols-3 gap-2 pt-4 border-t">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ site.buildings_count || 0 }}</div>
                                    <div class="text-xs text-gray-500">Bâtiments</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ site.boxes_count || 0 }}</div>
                                    <div class="text-xs text-gray-500">Boxes</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">
                                        {{ site.plan_enabled ? '✓' : '✗' }}
                                    </div>
                                    <div class="text-xs text-gray-500">Plan Visuel</div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t">
                            <Link
                                :href="route('admin.sites.show', site.id)"
                                class="text-sm text-blue-600 hover:underline font-medium"
                            >
                                📊 Voir Détails
                            </Link>
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('admin.sites.edit', site.id)"
                                    class="text-sm text-gray-600 hover:text-gray-900"
                                >
                                    ✏️ Modifier
                                </Link>
                                <Link
                                    :href="route('admin.floor-plan.index', { site_id: site.id })"
                                    class="text-sm text-green-600 hover:text-green-800 font-medium"
                                >
                                    🎨 Plan
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Message si aucun site -->
                    <div v-if="sites.length === 0" class="col-span-3">
                        <div class="bg-white shadow rounded-lg p-12 text-center">
                            <div class="text-6xl mb-4">🏢</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun site pour le moment</h3>
                            <p class="text-gray-600 mb-6">
                                Commencez par créer votre premier site de self-storage
                            </p>
                            <Link
                                :href="route('admin.sites.create')"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                ➕ Créer mon Premier Site
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
