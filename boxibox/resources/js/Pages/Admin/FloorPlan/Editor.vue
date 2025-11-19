<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    floor: Object,
    boxes: Array,
    building: Object,
    site: Object,
});

const page = usePage();

// État local
const boxes = ref([...props.boxes]);
const selectedBox = ref(null);
const isDragging = ref(false);
const dragStartPos = ref({ x: 0, y: 0 });
const canvasScale = ref(1);
const canvasOffset = ref({ x: 0, y: 0 });
const showSettings = ref(false);
const showBoxForm = ref(false);

// Paramètres du plan
const planWidth = ref(props.floor.plan_width || 1200);
const planHeight = ref(props.floor.plan_height || 800);

// Fonction pour obtenir la couleur d'une box selon son statut
const getBoxColor = (box) => {
    if (box.plan_color) return box.plan_color;

    switch (box.status) {
        case 'available':
            return '#10B981'; // green-500
        case 'occupied':
            return '#EF4444'; // red-500
        case 'reserved':
            return '#F59E0B'; // amber-500
        case 'maintenance':
            return '#6B7280'; // gray-500
        default:
            return '#3B82F6'; // blue-500
    }
};

// Démarrer le drag d'une box
const startDrag = (box, event) => {
    isDragging.value = true;
    selectedBox.value = box;

    const rect = event.currentTarget.getBoundingClientRect();
    dragStartPos.value = {
        x: event.clientX - rect.left,
        y: event.clientY - rect.top
    };
};

// Drag en cours
const onDrag = (event) => {
    if (!isDragging.value || !selectedBox.value) return;

    const canvas = document.getElementById('floor-plan-canvas');
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    let newX = event.clientX - rect.left - dragStartPos.value.x;
    let newY = event.clientY - rect.top - dragStartPos.value.y;

    // Contraintes: rester dans le canvas
    newX = Math.max(0, Math.min(newX, planWidth.value - selectedBox.value.plan_width));
    newY = Math.max(0, Math.min(newY, planHeight.value - selectedBox.value.plan_height));

    selectedBox.value.plan_x = Math.round(newX);
    selectedBox.value.plan_y = Math.round(newY);
};

// Fin du drag
const stopDrag = () => {
    if (isDragging.value && selectedBox.value) {
        // Sauvegarder la position
        saveBoxPosition(selectedBox.value);
    }

    isDragging.value = false;
    selectedBox.value = null;
};

// Sauvegarder la position d'une box
const saveBoxPosition = (box) => {
    router.patch(route('admin.floors.boxes.update', [props.floor.id, box.id]), {
        plan_x: box.plan_x,
        plan_y: box.plan_y,
        plan_width: box.plan_width,
        plan_height: box.plan_height,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Position sauvegardée');
        }
    });
};

// Sélectionner une box
const selectBox = (box) => {
    selectedBox.value = selectedBox.value?.id === box.id ? null : box;
};

// Sauvegarder toutes les positions
const saveAllPositions = () => {
    const boxesData = boxes.value.map(box => ({
        id: box.id,
        plan_x: box.plan_x || 0,
        plan_y: box.plan_y || 0,
        plan_width: box.plan_width || 100,
        plan_height: box.plan_height || 100,
    }));

    router.post(route('admin.floor-plan.update-positions', props.floor.id), {
        boxes: boxesData
    }, {
        preserveScroll: true,
        onSuccess: () => {
            alert('Toutes les positions ont été sauvegardées !');
        }
    });
};

// Auto-organiser les boxes
const autoOrganize = (layout = 'grid') => {
    if (!confirm(`Réorganiser automatiquement toutes les boxes en mode ${layout} ?`)) {
        return;
    }

    router.post(route('admin.floor-plan.auto-organize', props.floor.id), {
        layout: layout,
        spacing: 20,
        box_width: 100,
        box_height: 100,
    }, {
        onSuccess: () => {
            window.location.reload();
        }
    });
};

// Changer la couleur d'une box
const changeBoxColor = (box, color) => {
    box.plan_color = color;
    router.patch(route('admin.floors.boxes.update-color', [props.floor.id, box.id]), {
        plan_color: color
    }, {
        preserveScroll: true
    });
};

// Zoom
const zoomIn = () => {
    canvasScale.value = Math.min(canvasScale.value + 0.1, 2);
};

const zoomOut = () => {
    canvasScale.value = Math.max(canvasScale.value - 0.1, 0.5);
};

const resetZoom = () => {
    canvasScale.value = 1;
};
</script>

<template>
    <AdminLayout :title="`Éditeur de Plan - ${floor.name}`">
        <div class="py-6">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Breadcrumb -->
                <nav class="mb-6 text-sm">
                    <ol class="flex items-center space-x-2">
                        <li><a :href="route('admin.sites.index')" class="text-blue-600 hover:underline">Sites</a></li>
                        <li class="text-gray-500">/</li>
                        <li><a :href="route('admin.sites.show', site.id)" class="text-blue-600 hover:underline">{{ site.name }}</a></li>
                        <li class="text-gray-500">/</li>
                        <li><a :href="route('admin.sites.buildings.show', [site.id, building.id])" class="text-blue-600 hover:underline">{{ building.name }}</a></li>
                        <li class="text-gray-500">/</li>
                        <li class="text-gray-700 font-medium">{{ floor.name }}</li>
                    </ol>
                </nav>

                <!-- Barre d'outils -->
                <div class="bg-white shadow rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">

                        <!-- Actions principales -->
                        <div class="flex items-center gap-2">
                            <button
                                @click="saveAllPositions"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                            >
                                💾 Sauvegarder Tout
                            </button>

                            <div class="relative inline-block">
                                <button
                                    @click="showSettings = !showSettings"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition"
                                >
                                    ⚙️ Auto-Organiser
                                </button>
                                <div v-if="showSettings" class="absolute z-10 mt-2 w-48 bg-white rounded-md shadow-lg">
                                    <button @click="autoOrganize('grid')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">🔲 Grille</button>
                                    <button @click="autoOrganize('rows')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">⬇️ Lignes</button>
                                    <button @click="autoOrganize('columns')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">➡️ Colonnes</button>
                                </div>
                            </div>
                        </div>

                        <!-- Zoom -->
                        <div class="flex items-center gap-2">
                            <button @click="zoomOut" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">🔍-</button>
                            <span class="text-sm">{{ Math.round(canvasScale * 100) }}%</span>
                            <button @click="zoomIn" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">🔍+</button>
                            <button @click="resetZoom" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">🔄</button>
                        </div>

                        <!-- Statistiques -->
                        <div class="flex items-center gap-4 text-sm">
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-green-500"></span>
                                Disponibles: {{ boxes.filter(b => b.status === 'available').length }}
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-red-500"></span>
                                Occupées: {{ boxes.filter(b => b.status === 'occupied').length }}
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-amber-500"></span>
                                Réservées: {{ boxes.filter(b => b.status === 'reserved').length }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                    <!-- Canvas du plan -->
                    <div class="lg:col-span-3">
                        <div class="bg-white shadow rounded-lg p-4">
                            <div class="relative overflow-auto border-2 border-gray-300 rounded" style="max-height: 80vh;">
                                <div
                                    id="floor-plan-canvas"
                                    class="relative bg-gray-50"
                                    :style="{
                                        width: planWidth + 'px',
                                        height: planHeight + 'px',
                                        transform: `scale(${canvasScale})`,
                                        transformOrigin: 'top left'
                                    }"
                                    @mousemove="onDrag"
                                    @mouseup="stopDrag"
                                    @mouseleave="stopDrag"
                                >
                                    <!-- Grille de fond -->
                                    <svg class="absolute inset-0 pointer-events-none" :width="planWidth" :height="planHeight">
                                        <defs>
                                            <pattern id="grid" :width="50" :height="50" patternUnits="userSpaceOnUse">
                                                <path d="M 50 0 L 0 0 0 50" fill="none" stroke="#e5e7eb" stroke-width="1"/>
                                            </pattern>
                                        </defs>
                                        <rect width="100%" height="100%" fill="url(#grid)" />
                                    </svg>

                                    <!-- Boxes -->
                                    <div
                                        v-for="box in boxes"
                                        :key="box.id"
                                        class="absolute border-2 rounded cursor-move transition-all"
                                        :class="{
                                            'border-blue-600 shadow-lg z-10': selectedBox?.id === box.id,
                                            'border-gray-300 hover:border-blue-400': selectedBox?.id !== box.id
                                        }"
                                        :style="{
                                            left: (box.plan_x || 0) + 'px',
                                            top: (box.plan_y || 0) + 'px',
                                            width: (box.plan_width || 100) + 'px',
                                            height: (box.plan_height || 100) + 'px',
                                            backgroundColor: getBoxColor(box),
                                        }"
                                        @mousedown="startDrag(box, $event)"
                                        @click.stop="selectBox(box)"
                                    >
                                        <div class="flex flex-col items-center justify-center h-full text-white text-xs font-bold p-1 text-center">
                                            <div>📦 {{ box.number }}</div>
                                            <div class="text-[10px] opacity-90">{{ box.size }}m²</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panneau latéral -->
                    <div class="lg:col-span-1">
                        <div class="bg-white shadow rounded-lg p-4 sticky top-4">
                            <h3 class="text-lg font-semibold mb-4">
                                {{ selectedBox ? `Box ${selectedBox.number}` : 'Aucune sélection' }}
                            </h3>

                            <div v-if="selectedBox" class="space-y-4">
                                <!-- Informations de la box -->
                                <div class="text-sm space-y-2">
                                    <div><strong>Taille:</strong> {{ selectedBox.size }}m²</div>
                                    <div><strong>Type:</strong> {{ selectedBox.type }}</div>
                                    <div><strong>Statut:</strong>
                                        <span :class="{
                                            'text-green-600': selectedBox.status === 'available',
                                            'text-red-600': selectedBox.status === 'occupied',
                                            'text-amber-600': selectedBox.status === 'reserved',
                                            'text-gray-600': selectedBox.status === 'maintenance'
                                        }">
                                            {{ selectedBox.status }}
                                        </span>
                                    </div>
                                    <div><strong>Prix:</strong> {{ selectedBox.current_price }}€/mois</div>
                                </div>

                                <!-- Position -->
                                <div class="border-t pt-4">
                                    <h4 class="font-semibold mb-2">Position</h4>
                                    <div class="space-y-2 text-sm">
                                        <div>X: {{ selectedBox.plan_x || 0 }}px</div>
                                        <div>Y: {{ selectedBox.plan_y || 0 }}px</div>
                                        <div>Largeur: {{ selectedBox.plan_width || 100 }}px</div>
                                        <div>Hauteur: {{ selectedBox.plan_height || 100 }}px</div>
                                    </div>
                                </div>

                                <!-- Couleur personnalisée -->
                                <div class="border-t pt-4">
                                    <h4 class="font-semibold mb-2">Couleur</h4>
                                    <div class="grid grid-cols-5 gap-2">
                                        <button
                                            v-for="color in ['#10B981', '#EF4444', '#F59E0B', '#3B82F6', '#8B5CF6', '#EC4899', '#6B7280']"
                                            :key="color"
                                            @click="changeBoxColor(selectedBox, color)"
                                            class="w-8 h-8 rounded border-2"
                                            :class="selectedBox.plan_color === color ? 'border-black' : 'border-gray-300'"
                                            :style="{ backgroundColor: color }"
                                        ></button>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="border-t pt-4 space-y-2">
                                    <a
                                        :href="route('admin.floors.boxes.edit', [floor.id, selectedBox.id])"
                                        class="block w-full px-4 py-2 bg-blue-600 text-white text-center rounded hover:bg-blue-700 transition"
                                    >
                                        ✏️ Modifier
                                    </a>
                                    <button
                                        @click="selectedBox = null"
                                        class="block w-full px-4 py-2 bg-gray-200 text-gray-700 text-center rounded hover:bg-gray-300 transition"
                                    >
                                        ❌ Désélectionner
                                    </button>
                                </div>
                            </div>

                            <div v-else class="text-center text-gray-500 py-8">
                                Cliquez sur une box pour la sélectionner
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.cursor-move {
    cursor: move;
}
</style>
