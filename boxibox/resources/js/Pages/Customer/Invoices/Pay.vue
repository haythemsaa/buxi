<template>
  <CustomerLayout title="Payer ma Facture">
    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <!-- Invoice Details -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Facture {{ invoice.invoice_number }}</h2>

            <div class="grid grid-cols-2 gap-4 mb-6">
              <div>
                <div class="text-sm text-gray-500">Date de facture</div>
                <div class="text-lg font-medium">{{ formatDate(invoice.invoice_date) }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-500">Date d'échéance</div>
                <div class="text-lg font-medium">{{ formatDate(invoice.due_date) }}</div>
              </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
              <div class="flex justify-between items-center">
                <span class="text-lg text-gray-700">Montant total</span>
                <span class="text-3xl font-bold text-indigo-600">
                  {{ formatCurrency(invoice.total_ttc) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Mode de paiement</h3>

            <form @submit.prevent="submitPayment">
              <!-- Gateway Selection -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Choisissez votre mode de paiement</label>
                <div class="space-y-3">
                  <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50" :class="form.gateway === 'stripe' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                    <input type="radio" v-model="form.gateway" value="stripe" class="mr-3" />
                    <div class="flex-1">
                      <div class="font-medium">Carte bancaire</div>
                      <div class="text-sm text-gray-500">Visa, Mastercard, American Express</div>
                    </div>
                    <svg class="w-16 h-10" viewBox="0 0 48 32" fill="none">
                      <rect width="48" height="32" rx="4" fill="#635BFF"/>
                      <text x="24" y="20" text-anchor="middle" fill="white" font-family="Arial" font-weight="bold" font-size="10">Stripe</text>
                    </svg>
                  </label>

                  <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50" :class="form.gateway === 'paypal' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                    <input type="radio" v-model="form.gateway" value="paypal" class="mr-3" />
                    <div class="flex-1">
                      <div class="font-medium">PayPal</div>
                      <div class="text-sm text-gray-500">Compte PayPal ou carte bancaire</div>
                    </div>
                    <svg class="w-16 h-10" viewBox="0 0 48 32">
                      <path fill="#003087" d="M18.8 8.6c-1 5.3-4.3 7.9-8.5 7.9H8.4L7 24.7c-.1.4.2.7.6.7h3.7c.5 0 .9-.3 1-.8l.9-5.5c.1-.5.5-.8 1-.8h2.3c4.7 0 8.4-1.9 9.5-7.4.5-2.4.2-4.3-1-5.6-.6-.7-1.5-1.2-2.5-1.6h2.8c.5 0 .9.3 1 .8l.5 2.1z"/>
                      <path fill="#0070E0" d="M14.3 8.6c-.2-.5-.6-.8-1-.8H6.9c-.5 0-1 .4-1.1.9L3.4 24.3c-.1.5.2.9.7.9h4.3c.4 0 .7-.3.8-.7l1.3-8.1c.1-.5.5-.9 1-.9h2.2c4.2 0 7.5-2.6 8.5-7.9z"/>
                    </svg>
                  </label>

                  <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50" :class="form.gateway === 'sepa' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                    <input type="radio" v-model="form.gateway" value="sepa" class="mr-3" />
                    <div class="flex-1">
                      <div class="font-medium">Prélèvement SEPA</div>
                      <div class="text-sm text-gray-500">Prélèvement automatique</div>
                    </div>
                    <div class="w-16 h-10 flex items-center justify-center bg-blue-600 rounded">
                      <span class="text-white font-bold text-sm">SEPA</span>
                    </div>
                  </label>
                </div>
              </div>

              <!-- Stripe Card Element -->
              <div v-if="form.gateway === 'stripe'" class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Informations de carte</label>
                <div id="card-element" class="p-3 border border-gray-300 rounded-md bg-white"></div>
                <div v-if="cardError" class="mt-2 text-sm text-red-600">{{ cardError }}</div>
              </div>

              <!-- PayPal Info -->
              <div v-if="form.gateway === 'paypal'" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-blue-800">
                  Vous serez redirigé vers PayPal pour finaliser le paiement de manière sécurisée.
                </p>
              </div>

              <!-- SEPA Info -->
              <div v-if="form.gateway === 'sepa'" class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-md">
                <p class="text-sm text-gray-700">
                  Le prélèvement sera effectué sur le compte bancaire enregistré dans votre profil.
                </p>
              </div>

              <!-- Save Payment Method -->
              <div v-if="form.gateway === 'stripe'" class="mb-6">
                <label class="flex items-center">
                  <input type="checkbox" v-model="form.save_payment_method" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                  <span class="ml-2 text-sm text-gray-600">Enregistrer cette carte pour les prochains paiements</span>
                </label>
              </div>

              <!-- Submit Button -->
              <div class="flex justify-between items-center">
                <Link :href="route('customer.invoices.index')" class="text-gray-600 hover:text-gray-900">
                  Retour aux factures
                </Link>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="form.processing">Traitement en cours...</span>
                  <span v-else>Payer {{ formatCurrency(invoice.total_ttc) }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
          <div class="flex">
            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <p class="text-sm text-gray-600">
              Vos informations de paiement sont sécurisées et cryptées. Nous n'enregistrons jamais les détails complets de votre carte.
            </p>
          </div>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
  invoice: Object,
});

const form = useForm({
  gateway: 'stripe',
  payment_method_id: '',
  save_payment_method: false,
});

const cardError = ref('');
let stripe = null;
let cardElement = null;

onMounted(() => {
  // Initialize Stripe
  if (window.Stripe && form.gateway === 'stripe') {
    initializeStripe();
  }
});

const initializeStripe = () => {
  stripe = window.Stripe(import.meta.env.VITE_STRIPE_KEY);
  const elements = stripe.elements();
  cardElement = elements.create('card', {
    style: {
      base: {
        fontSize: '16px',
        color: '#32325d',
      },
    },
  });
  cardElement.mount('#card-element');
  cardElement.on('change', (event) => {
    cardError.value = event.error ? event.error.message : '';
  });
};

const submitPayment = async () => {
  if (form.gateway === 'stripe') {
    const { paymentMethod, error } = await stripe.createPaymentMethod({
      type: 'card',
      card: cardElement,
    });

    if (error) {
      cardError.value = error.message;
      return;
    }

    form.payment_method_id = paymentMethod.id;
  }

  form.post(route('customer.invoices.pay', props.invoice.id));
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR');
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
  }).format(amount);
};
</script>
