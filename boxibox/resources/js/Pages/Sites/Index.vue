<template>
    <AppLayout>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Sites de stockage</h1>
            <Link :href="route('sites.create')" class="btn btn-primary">
                Nouveau site
            </Link>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nom
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Adresse
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Bâtiments
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Boxes
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Taux d'occupation
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="site in sites.data" :key="site.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ site.name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ site.address }}</div>
                            <div class="text-sm text-gray-500">{{ site.postal_code }} {{ site.city }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ site.buildings_count || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ site.boxes_count || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm text-gray-900">{{ site.occupancy_rate || 0 }}%</div>
                                <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                    <div
                                        class="bg-blue-600 h-2 rounded-full"
                                        :style="{ width: (site.occupancy_rate || 0) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link :href="route('sites.show', site.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                Voir
                            </Link>
                            <Link :href="route('sites.edit', site.id)" class="text-blue-600 hover:text-blue-900 mr-3">
                                Éditer
                            </Link>
                            <button @click="deleteSite(site.id)" class="text-red-600 hover:text-red-900">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="sites.links && sites.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Affichage de {{ sites.from }} à {{ sites.to }} sur {{ sites.total }} résultats
                    </div>
                    <div class="flex space-x-1">
                        <Link
                            v-for="(link, index) in sites.links"
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
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    sites: Object,
});

const deleteSite = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce site ?')) {
        router.delete(route('sites.destroy', id));
    }
};
</script>
