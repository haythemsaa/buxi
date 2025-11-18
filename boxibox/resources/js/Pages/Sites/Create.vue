<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Créer un nouveau site</h1>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom du site *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.name }"
                            required
                        />
                        <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                    </div>

                    <!-- Address -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adresse *</label>
                        <input
                            v-model="form.address"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.address }"
                            required
                        />
                        <div v-if="form.errors.address" class="text-red-600 text-sm mt-1">{{ form.errors.address }}</div>
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Code postal *</label>
                        <input
                            v-model="form.postal_code"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.postal_code }"
                            required
                        />
                        <div v-if="form.errors.postal_code" class="text-red-600 text-sm mt-1">{{ form.errors.postal_code }}</div>
                    </div>

                    <!-- City -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ville *</label>
                        <input
                            v-model="form.city"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.city }"
                            required
                        />
                        <div v-if="form.errors.city" class="text-red-600 text-sm mt-1">{{ form.errors.city }}</div>
                    </div>

                    <!-- Country -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                        <input
                            v-model="form.country"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.country }"
                        />
                        <div v-if="form.errors.country" class="text-red-600 text-sm mt-1">{{ form.errors.country }}</div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.phone }"
                        />
                        <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">{{ form.errors.phone }}</div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.email }"
                        />
                        <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
                    </div>

                    <!-- GPS Latitude -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Latitude GPS</label>
                        <input
                            v-model="form.gps_latitude"
                            type="number"
                            step="0.000001"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.gps_latitude }"
                        />
                        <div v-if="form.errors.gps_latitude" class="text-red-600 text-sm mt-1">{{ form.errors.gps_latitude }}</div>
                    </div>

                    <!-- GPS Longitude -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Longitude GPS</label>
                        <input
                            v-model="form.gps_longitude"
                            type="number"
                            step="0.000001"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.gps_longitude }"
                        />
                        <div v-if="form.errors.gps_longitude" class="text-red-600 text-sm mt-1">{{ form.errors.gps_longitude }}</div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('sites.index')" class="btn btn-secondary">
                        Annuler
                    </Link>
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        Créer le site
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    name: '',
    address: '',
    postal_code: '',
    city: '',
    country: 'France',
    phone: '',
    email: '',
    gps_latitude: null,
    gps_longitude: null,
});

const submit = () => {
    form.post(route('sites.store'));
};
</script>
