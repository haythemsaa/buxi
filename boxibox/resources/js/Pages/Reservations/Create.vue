<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Nouvelle réservation</h1>
            <p class="text-gray-500 mt-1">Complétez votre réservation</p>
        </div>

        <form @submit.prevent="submitReservation">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Box Information (readonly) -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Box sélectionné</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Numéro</label>
                                <p class="text-lg text-gray-900 font-mono">{{ box.number }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Site</label>
                                <p class="text-lg text-gray-900">{{ box.site_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Volume</label>
                                <p class="text-lg text-gray-900">{{ box.volume.toFixed(2) }} m³</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Catégorie</label>
                                <p class="text-lg text-gray-900">{{ getSizeCategoryLabel(box.size_category) }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span v-if="box.climate_controlled" class="badge bg-info">Climatisé</span>
                            <span v-if="box.ground_floor" class="badge bg-secondary">RDC</span>
                            <span v-if="box.vehicle_access" class="badge bg-secondary">Accès véhicule</span>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Détails de la réservation</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date de début *</label>
                                <input v-model="form.start_date" type="date" required
                                       :min="minDate"
                                       class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Durée (mois) *</label>
                                <input v-model="form.duration_months" type="number" min="1" max="24" required
                                       @input="calculatePrice"
                                       class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Vos informations</h2>
                        <div v-if="!$page.props.auth.user">
                            <p class="text-sm text-gray-600 mb-4">
                                Vous avez déjà un compte ?
                                <Link :href="route('login')" class="text-indigo-600 hover:text-indigo-900">
                                    Connectez-vous
                                </Link>
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                                <input v-model="form.guest_first_name" type="text" required
                                       :disabled="!!$page.props.auth.user"
                                       class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                                <input v-model="form.guest_last_name" type="text" required
                                       :disabled="!!$page.props.auth.user"
                                       class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input v-model="form.guest_email" type="email" required
                                       :disabled="!!$page.props.auth.user"
                                       class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                                <input v-model="form.guest_phone" type="tel" required
                                       class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Options</h2>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input v-model="form.insurance" type="checkbox" id="insurance"
                                           @change="calculatePrice"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                </div>
                                <div class="ml-3">
                                    <label for="insurance" class="font-medium text-gray-700">
                                        Assurance (15€/mois)
                                    </label>
                                    <p class="text-sm text-gray-500">
                                        Protégez vos biens jusqu'à 10 000€
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Promotion Code -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold mb-4">Code promo</h2>
                        <div class="flex gap-2">
                            <input v-model="promoCode" type="text" placeholder="Entrez votre code promo"
                                   class="flex-1 rounded-md border-gray-300 shadow-sm">
                            <button type="button" @click="validatePromo"
                                    :disabled="validatingPromo"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                {{ validatingPromo ? 'Validation...' : 'Appliquer' }}
                            </button>
                        </div>
                        <div v-if="promoError" class="mt-2 text-sm text-red-600">
                            {{ promoError }}
                        </div>
                        <div v-if="form.promo_code && promoDetails" class="mt-3 p-3 bg-green-50 border border-green-200 rounded">
                            <p class="text-sm text-green-800">
                                ✓ {{ promoDetails.name }} appliqué : {{ promoDetails.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Price Summary -->
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                        <h2 class="text-xl font-semibold mb-4">Récapitulatif</h2>

                        <div v-if="loadingPrice" class="text-center py-8">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                            <p class="text-gray-500 mt-2">Calcul en cours...</p>
                        </div>

                        <div v-else-if="pricing" class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Prix mensuel HT</span>
                                <span class="font-medium">{{ formatCurrency(pricing.monthly_price_ht) }}</span>
                            </div>
                            <div v-if="form.insurance" class="flex justify-between">
                                <span class="text-gray-600">Assurance</span>
                                <span class="font-medium">{{ formatCurrency(pricing.insurance_monthly) }}</span>
                            </div>
                            <div v-if="pricing.discount_amount > 0" class="flex justify-between text-green-600">
                                <span>Réduction</span>
                                <span class="font-medium">-{{ formatCurrency(pricing.discount_amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">TVA ({{ pricing.tax_rate }}%)</span>
                                <span class="font-medium">{{ formatCurrency(pricing.tax_amount) }}</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between">
                                <span class="font-bold">Prix mensuel TTC</span>
                                <span class="font-bold text-lg">{{ formatCurrency(pricing.total_monthly_ttc) }}</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between">
                                <span class="font-bold">Dépôt de garantie</span>
                                <span class="font-bold">{{ formatCurrency(pricing.deposit) }}</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between">
                                <span class="font-bold text-indigo-600">Premier paiement</span>
                                <span class="font-bold text-lg text-indigo-600">
                                    {{ formatCurrency(pricing.first_payment) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            <button type="submit"
                                    :disabled="submitting || loadingPrice"
                                    class="w-full bg-indigo-600 text-white px-4 py-3 rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                {{ submitting ? 'Envoi en cours...' : 'Confirmer la réservation' }}
                            </button>
                            <Link :href="route('reservations.index')"
                                  class="block text-center text-gray-600 hover:text-gray-900">
                                Annuler
                            </Link>
                        </div>

                        <p class="text-xs text-gray-500 mt-4">
                            En confirmant cette réservation, vous acceptez nos conditions générales.
                            La réservation sera valable pendant 30 jours.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    box: Object,
    initialDuration: {
        type: Number,
        default: 1,
    },
});

const page = usePage();
const submitting = ref(false);
const loadingPrice = ref(false);
const validatingPromo = ref(false);
const promoCode = ref('');
const promoError = ref('');
const promoDetails = ref(null);
const pricing = ref(null);

const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

const form = reactive({
    box_id: props.box.id,
    start_date: minDate.value,
    duration_months: props.initialDuration || 1,
    guest_first_name: page.props.auth.user?.name?.split(' ')[0] || '',
    guest_last_name: page.props.auth.user?.name?.split(' ').slice(1).join(' ') || '',
    guest_email: page.props.auth.user?.email || '',
    guest_phone: '',
    insurance: false,
    promo_code: '',
});

onMounted(() => {
    calculatePrice();
});

const calculatePrice = async () => {
    loadingPrice.value = true;
    try {
        const response = await axios.post(route('reservations.calculate-price'), {
            box_id: form.box_id,
            duration_months: form.duration_months,
            insurance: form.insurance,
            promo_code: form.promo_code,
        });
        pricing.value = response.data.pricing;
    } catch (error) {
        console.error('Price calculation error:', error);
    } finally {
        loadingPrice.value = false;
    }
};

const validatePromo = async () => {
    if (!promoCode.value) return;

    validatingPromo.value = true;
    promoError.value = '';
    promoDetails.value = null;

    try {
        const response = await axios.post(route('reservations.calculate-price'), {
            box_id: form.box_id,
            duration_months: form.duration_months,
            insurance: form.insurance,
            promo_code: promoCode.value,
        });

        if (response.data.promotion) {
            form.promo_code = promoCode.value;
            promoDetails.value = response.data.promotion;
            pricing.value = response.data.pricing;
        } else {
            promoError.value = 'Code promo invalide';
        }
    } catch (error) {
        promoError.value = error.response?.data?.message || 'Code promo invalide';
    } finally {
        validatingPromo.value = false;
    }
};

const submitReservation = () => {
    if (submitting.value) return;

    submitting.value = true;
    router.post(route('reservations.store'), form, {
        onFinish: () => {
            submitting.value = false;
        },
    });
};

const formatCurrency = (amount) => {
    if (!amount) return '0,00 €';
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount);
};

const getSizeCategoryLabel = (category) => {
    const labels = {
        mini: 'Mini',
        small: 'Petit',
        medium: 'Moyen',
        large: 'Grand',
        xl: 'Très grand',
    };
    return labels[category] || category;
};
</script>
