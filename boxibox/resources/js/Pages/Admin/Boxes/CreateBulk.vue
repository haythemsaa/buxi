<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    floor: Object,
});

const form = useForm({
    prefix: '',
    start_number: 1,
    count: 10,
    size: 10,
    type: 'standard',
    base_price: 100,
    auto_organize: true,
    layout: 'grid',
});

const submit = () => {
    form.post(route('admin.floors.boxes.store-bulk', props.floor.id), {
        onSuccess: () => {
            // Rediriger vers l'éditeur de plan
            window.location.href = route('admin.floor-plan.edit', props.floor.id);
        }
    });
};

const totalBoxes = () => {
    const start = parseInt(form.start_number) || 1;
    const count = parseInt(form.count) || 0;
    const examples = [];

    for (let i = 0; i < Math.min(count, 5); i++) {
        examples.push(`${form.prefix}${start + i}`);
    }

    if (count > 5) {
        examples.push('...');
        examples.push(`${form.prefix}${start + count - 1}`);
    }

    return examples.join(', ');
};
</script>

<template>
    <AdminLayout title="Création en Masse de Boxes">
        <div class="py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Breadcrumb -->
                <nav class="mb-6 text-sm">
                    <ol class="flex items-center space-x-2">
                        <li><a :href="route('admin.sites.index')" class="text-blue-600 hover:underline">Sites</a></li>
                        <li class="text-gray-500">/</li>
                        <li><a :href="route('admin.sites.show', floor.building.site_id)" class="text-blue-600 hover:underline">
                            {{ floor.building.site.name }}
                        </a></li>
                        <li class="text-gray-500">/</li>
                        <li><a :href="route('admin.sites.buildings.show', [floor.building.site_id, floor.building_id])" class="text-blue-600 hover:underline">
                            {{ floor.building.name }}
                        </a></li>
                        <li class="text-gray-500">/</li>
                        <li class="text-gray-700 font-medium">{{ floor.name }} - Création en Masse</li>
                    </ol>
                </nav>

                <div class="bg-white shadow rounded-lg p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Création Rapide de Boxes</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Créez plusieurs boxes à la fois avec une numérotation automatique
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Numérotation -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-blue-900 mb-4">📋 Numérotation</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="prefix" value="Préfixe (optionnel)" />
                                    <TextInput
                                        id="prefix"
                                        v-model="form.prefix"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Ex: A, RDC-, B1-"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Sera ajouté avant chaque numéro</p>
                                </div>

                                <div>
                                    <InputLabel for="start_number" value="Numéro de départ *" />
                                    <TextInput
                                        id="start_number"
                                        v-model="form.start_number"
                                        type="number"
                                        class="mt-1 block w-full"
                                        required
                                        min="1"
                                    />
                                    <InputError :message="form.errors.start_number" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="count" value="Nombre de boxes *" />
                                    <TextInput
                                        id="count"
                                        v-model="form.count"
                                        type="number"
                                        class="mt-1 block w-full"
                                        required
                                        min="1"
                                        max="100"
                                    />
                                    <InputError :message="form.errors.count" class="mt-2" />
                                </div>
                            </div>

                            <!-- Aperçu -->
                            <div v-if="form.count > 0" class="mt-4 p-3 bg-white rounded border">
                                <p class="text-sm font-medium text-gray-700 mb-1">Aperçu des numéros :</p>
                                <p class="text-sm text-gray-600">{{ totalBoxes() }}</p>
                            </div>
                        </div>

                        <!-- Caractéristiques -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-green-900 mb-4">📦 Caractéristiques</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="size" value="Taille (m²) *" />
                                    <TextInput
                                        id="size"
                                        v-model="form.size"
                                        type="number"
                                        step="0.1"
                                        class="mt-1 block w-full"
                                        required
                                        min="1"
                                    />
                                    <InputError :message="form.errors.size" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="type" value="Type *" />
                                    <select
                                        id="type"
                                        v-model="form.type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="standard">Standard</option>
                                        <option value="climate_controlled">Climatisé</option>
                                        <option value="premium">Premium</option>
                                        <option value="outdoor">Extérieur</option>
                                    </select>
                                    <InputError :message="form.errors.type" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="base_price" value="Prix de base (€/mois) *" />
                                    <TextInput
                                        id="base_price"
                                        v-model="form.base_price"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        required
                                        min="0"
                                    />
                                    <InputError :message="form.errors.base_price" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Organisation automatique -->
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-purple-900 mb-4">🎨 Organisation sur le Plan</h3>

                            <div class="flex items-center mb-4">
                                <input
                                    id="auto_organize"
                                    v-model="form.auto_organize"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <label for="auto_organize" class="ml-2 text-sm text-gray-700">
                                    Organiser automatiquement sur le plan
                                </label>
                            </div>

                            <div v-if="form.auto_organize" class="grid grid-cols-3 gap-3">
                                <button
                                    type="button"
                                    @click="form.layout = 'grid'"
                                    class="p-3 border-2 rounded-lg text-center transition"
                                    :class="form.layout === 'grid' ? 'border-purple-600 bg-purple-100' : 'border-gray-300 hover:border-purple-400'"
                                >
                                    <div class="text-2xl mb-1">🔲</div>
                                    <div class="text-sm font-medium">Grille</div>
                                    <div class="text-xs text-gray-500">Disposition optimale</div>
                                </button>

                                <button
                                    type="button"
                                    @click="form.layout = 'rows'"
                                    class="p-3 border-2 rounded-lg text-center transition"
                                    :class="form.layout === 'rows' ? 'border-purple-600 bg-purple-100' : 'border-gray-300 hover:border-purple-400'"
                                >
                                    <div class="text-2xl mb-1">⬇️</div>
                                    <div class="text-sm font-medium">Lignes</div>
                                    <div class="text-xs text-gray-500">Une colonne</div>
                                </button>

                                <button
                                    type="button"
                                    @click="form.layout = 'columns'"
                                    class="p-3 border-2 rounded-lg text-center transition"
                                    :class="form.layout === 'columns' ? 'border-purple-600 bg-purple-100' : 'border-gray-300 hover:border-purple-400'"
                                >
                                    <div class="text-2xl mb-1">➡️</div>
                                    <div class="text-sm font-medium">Colonnes</div>
                                    <div class="text-xs text-gray-500">Une ligne</div>
                                </button>
                            </div>
                        </div>

                        <!-- Résumé -->
                        <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">📊 Résumé</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Nombre de boxes :</span>
                                    <span class="font-semibold ml-2">{{ form.count }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Prix unitaire :</span>
                                    <span class="font-semibold ml-2">{{ form.base_price }}€/mois</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Revenue mensuel potentiel :</span>
                                    <span class="font-semibold ml-2 text-green-600">{{ (form.count * form.base_price).toFixed(2) }}€</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Revenue annuel potentiel :</span>
                                    <span class="font-semibold ml-2 text-green-600">{{ (form.count * form.base_price * 12).toFixed(2) }}€</span>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <a
                                :href="route('admin.floor-plan.edit', floor.id)"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                ← Annuler
                            </a>

                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                class="text-lg px-8 py-3"
                            >
                                ✨ Créer {{ form.count }} Boxes
                            </PrimaryButton>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
