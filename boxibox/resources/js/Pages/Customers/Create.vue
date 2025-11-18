<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Créer un nouveau client</h1>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="submit">
                <!-- Customer Type -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de client *</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input
                                v-model="form.type"
                                type="radio"
                                value="individual"
                                class="form-check-input"
                            />
                            <span class="ml-2">Particulier</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input
                                v-model="form.type"
                                type="radio"
                                value="professional"
                                class="form-check-input"
                            />
                            <span class="ml-2">Professionnel</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Professional fields -->
                    <div v-if="form.type === 'professional'" class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Raison sociale *</label>
                        <input
                            v-model="form.company_name"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.company_name }"
                            required
                        />
                        <div v-if="form.errors.company_name" class="text-red-600 text-sm mt-1">{{ form.errors.company_name }}</div>
                    </div>

                    <div v-if="form.type === 'professional'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">N° SIRET</label>
                        <input
                            v-model="form.siret"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.siret }"
                        />
                        <div v-if="form.errors.siret" class="text-red-600 text-sm mt-1">{{ form.errors.siret }}</div>
                    </div>

                    <div v-if="form.type === 'professional'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">N° TVA</label>
                        <input
                            v-model="form.vat_number"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.vat_number }"
                        />
                        <div v-if="form.errors.vat_number" class="text-red-600 text-sm mt-1">{{ form.errors.vat_number }}</div>
                    </div>

                    <!-- Individual/Contact fields -->
                    <div :class="{ 'md:col-span-2': form.type === 'professional' }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ form.type === 'professional' ? 'Prénom du contact' : 'Prénom *' }}
                        </label>
                        <input
                            v-model="form.first_name"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.first_name }"
                            :required="form.type === 'individual'"
                        />
                        <div v-if="form.errors.first_name" class="text-red-600 text-sm mt-1">{{ form.errors.first_name }}</div>
                    </div>

                    <div :class="{ 'md:col-span-2': form.type === 'professional' }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ form.type === 'professional' ? 'Nom du contact' : 'Nom *' }}
                        </label>
                        <input
                            v-model="form.last_name"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.last_name }"
                            :required="form.type === 'individual'"
                        />
                        <div v-if="form.errors.last_name" class="text-red-600 text-sm mt-1">{{ form.errors.last_name }}</div>
                    </div>

                    <!-- Contact information -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.email }"
                            required
                        />
                        <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone *</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.phone }"
                            required
                        />
                        <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">{{ form.errors.phone }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone secondaire</label>
                        <input
                            v-model="form.phone_secondary"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.phone_secondary }"
                        />
                        <div v-if="form.errors.phone_secondary" class="text-red-600 text-sm mt-1">{{ form.errors.phone_secondary }}</div>
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
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('customers.index')" class="btn btn-secondary">
                        Annuler
                    </Link>
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        Créer le client
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
    type: 'individual',
    company_name: '',
    siret: '',
    vat_number: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    phone_secondary: '',
    address: '',
    postal_code: '',
    city: '',
    country: 'France',
});

const submit = () => {
    form.post(route('customers.store'));
};
</script>
