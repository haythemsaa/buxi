<template>
    <AppLayout>
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Réservation {{ reservation.reservation_number }}</h1>
                    <p class="text-gray-500 mt-1">
                        <span :class="getStatusBadgeClass(reservation.status)">
                            {{ getStatusLabel(reservation.status) }}
                        </span>
                    </p>
                </div>
                <div class="space-x-2">
                    <Link :href="route('reservations.index')" class="btn btn-secondary">
                        Retour à la recherche
                    </Link>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Reservation Details -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Détails de la réservation</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">N° Réservation</label>
                            <p class="text-lg text-gray-900 font-mono">{{ reservation.reservation_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Date de début</label>
                            <p class="text-lg text-gray-900">{{ formatDate(reservation.start_date) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Durée</label>
                            <p class="text-lg text-gray-900">{{ reservation.duration_months }} mois</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Expire le</label>
                            <p class="text-lg text-gray-900">{{ formatDate(reservation.expires_at) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Statut</label>
                            <p>
                                <span :class="getStatusBadgeClass(reservation.status)">
                                    {{ getStatusLabel(reservation.status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">
                        {{ reservation.customer ? 'Client' : 'Informations de contact' }}
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nom</label>
                            <p class="text-lg text-gray-900">
                                {{ reservation.customer ?
                                    `${reservation.customer.first_name} ${reservation.customer.last_name}` :
                                    `${reservation.guest_first_name} ${reservation.guest_last_name}` }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="text-lg text-gray-900">
                                {{ reservation.customer?.email || reservation.guest_email }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Téléphone</label>
                            <p class="text-lg text-gray-900">
                                {{ reservation.customer?.phone || reservation.guest_phone }}
                            </p>
                        </div>
                        <div v-if="!reservation.customer">
                            <label class="block text-sm font-medium text-gray-500">Type</label>
                            <p class="text-sm text-gray-600">Réservation sans compte</p>
                        </div>
                    </div>
                </div>

                <!-- Box Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Box réservé</h2>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Numéro</label>
                            <p class="text-lg text-gray-900 font-mono">{{ reservation.box?.number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Site</label>
                            <p class="text-lg text-gray-900">{{ reservation.site?.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Volume</label>
                            <p class="text-lg text-gray-900">{{ reservation.box?.volume.toFixed(2) }} m³</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Catégorie</label>
                            <p class="text-lg text-gray-900">{{ getSizeCategoryLabel(reservation.box?.size_category) }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span v-if="reservation.box?.climate_controlled" class="badge bg-info">Climatisé</span>
                        <span v-if="reservation.box?.ground_floor" class="badge bg-secondary">RDC</span>
                        <span v-if="reservation.box?.vehicle_access" class="badge bg-secondary">Accès véhicule</span>
                    </div>
                </div>

                <!-- Promotion -->
                <div v-if="reservation.promotion" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Promotion appliquée</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Code</label>
                            <p class="text-lg text-gray-900 font-mono">{{ reservation.promotion.code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nom</label>
                            <p class="text-lg text-gray-900">{{ reservation.promotion.name }}</p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Description</label>
                            <p class="text-gray-700">{{ reservation.promotion.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Pricing Summary -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Récapitulatif</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Prix mensuel HT</span>
                            <span class="font-medium">{{ formatCurrency(reservation.monthly_price_ht) }}</span>
                        </div>
                        <div v-if="reservation.discount_amount > 0" class="flex justify-between text-green-600">
                            <span>Réduction</span>
                            <span class="font-medium">-{{ formatCurrency(reservation.discount_amount) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">TVA ({{ reservation.tax_rate }}%)</span>
                            <span class="font-medium">{{ formatCurrency(calculateTax()) }}</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="font-bold">Prix mensuel TTC</span>
                            <span class="font-bold text-lg">{{ formatCurrency(calculateMonthlyTTC()) }}</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="font-bold">Dépôt de garantie</span>
                            <span class="font-bold">{{ formatCurrency(reservation.deposit_amount) }}</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="font-bold text-indigo-600">Premier paiement</span>
                            <span class="font-bold text-lg text-indigo-600">
                                {{ formatCurrency(reservation.first_payment) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div v-if="reservation.status === 'pending'" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Actions</h2>
                    <div class="space-y-3">
                        <button @click="confirmReservation"
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            Confirmer la réservation
                        </button>
                        <button @click="cancelReservation"
                                class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                            Annuler la réservation
                        </button>
                    </div>
                </div>

                <!-- Status Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">{{ getStatusTitle() }}</h3>
                    <p class="text-sm text-blue-700">{{ getStatusDescription() }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    reservation: Object,
});

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('fr-FR');
};

const formatCurrency = (amount) => {
    if (!amount) return '0,00 €';
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount);
};

const calculateTax = () => {
    const priceHT = props.reservation.monthly_price_ht - props.reservation.discount_amount;
    return priceHT * (props.reservation.tax_rate / 100);
};

const calculateMonthlyTTC = () => {
    const priceHT = props.reservation.monthly_price_ht - props.reservation.discount_amount;
    return priceHT * (1 + props.reservation.tax_rate / 100);
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'pending':
            return 'badge bg-warning';
        case 'confirmed':
            return 'badge bg-info';
        case 'converted':
            return 'badge bg-success';
        case 'expired':
            return 'badge bg-secondary';
        case 'cancelled':
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'En attente',
        confirmed: 'Confirmée',
        converted: 'Convertie en contrat',
        expired: 'Expirée',
        cancelled: 'Annulée',
    };
    return labels[status] || status;
};

const getStatusTitle = () => {
    const titles = {
        pending: 'Réservation en attente',
        confirmed: 'Réservation confirmée',
        converted: 'Contrat créé',
        expired: 'Réservation expirée',
        cancelled: 'Réservation annulée',
    };
    return titles[props.reservation.status] || 'Statut';
};

const getStatusDescription = () => {
    const descriptions = {
        pending: 'Cette réservation est valable jusqu\'au ' + formatDate(props.reservation.expires_at) + '. Confirmez-la pour créer un contrat.',
        confirmed: 'Cette réservation a été confirmée. Un contrat sera créé lors de la signature.',
        converted: 'Cette réservation a été convertie en contrat actif.',
        expired: 'Cette réservation a expiré et n\'est plus valable.',
        cancelled: 'Cette réservation a été annulée.',
    };
    return descriptions[props.reservation.status] || '';
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

const confirmReservation = () => {
    if (confirm('Êtes-vous sûr de vouloir confirmer cette réservation ?')) {
        router.post(route('reservations.confirm', props.reservation.id));
    }
};

const cancelReservation = () => {
    if (confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
        router.post(route('reservations.cancel', props.reservation.id));
    }
};
</script>
