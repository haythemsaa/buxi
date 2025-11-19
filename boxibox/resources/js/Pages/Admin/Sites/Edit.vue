<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    site: Object,
});

const form = useForm({
    name: props.site.name,
    address: props.site.address,
    city: props.site.city,
    postal_code: props.site.postal_code,
    phone: props.site.phone || '',
    email: props.site.email || '',
    status: props.site.status,
    plan_enabled: props.site.plan_enabled,
});

const submit = () => {
    form.put(route('admin.sites.update', props.site.id));
};
</script>

<template>
    <AdminLayout :title="`Modifier ${site.name}`">
        <div class="py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="bg-white shadow rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Nom -->
                        <div>
                            <InputLabel for="name" value="Nom du Site *" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <!-- Adresse -->
                        <div>
                            <InputLabel for="address" value="Adresse *" />
                            <TextInput
                                id="address"
                                v-model="form.address"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.address" class="mt-2" />
                        </div>

                        <!-- Ville et Code Postal -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="city" value="Ville *" />
                                <TextInput
                                    id="city"
                                    v-model="form.city"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.city" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="postal_code" value="Code Postal *" />
                                <TextInput
                                    id="postal_code"
                                    v-model="form.postal_code"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.postal_code" class="mt-2" />
                            </div>
                        </div>

                        <!-- Téléphone et Email -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="phone" value="Téléphone" />
                                <TextInput
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.phone" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>
                        </div>

                        <!-- Statut -->
                        <div>
                            <InputLabel for="status" value="Statut *" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            <InputError :message="form.errors.status" class="mt-2" />
                        </div>

                        <!-- Plan Visuel -->
                        <div class="flex items-center">
                            <input
                                id="plan_enabled"
                                v-model="form.plan_enabled"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <label for="plan_enabled" class="ml-2 text-sm text-gray-700">
                                Activer la gestion visuelle des plans
                            </label>
                        </div>

                        <!-- Boutons -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <a
                                :href="route('admin.sites.show', site.id)"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                ← Retour aux détails
                            </a>

                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Enregistrer les Modifications
                            </PrimaryButton>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
