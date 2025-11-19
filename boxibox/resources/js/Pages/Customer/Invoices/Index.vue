<template>
  <CustomerLayout title="Mes Factures">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-gray-900">Mes Factures</h2>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex gap-4">
              <select v-model="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="paid">Payées</option>
                <option value="overdue">En retard</option>
                <option value="cancelled">Annulées</option>
              </select>
            </div>

            <!-- Invoices List -->
            <div v-if="filteredInvoices.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Numéro
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Échéance
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Montant
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Statut
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="invoice in filteredInvoices" :key="invoice.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ invoice.invoice_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(invoice.invoice_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(invoice.due_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                      {{ formatCurrency(invoice.total_ttc) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusClass(invoice.status)">
                        {{ getStatusLabel(invoice.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                      <Link :href="route('customer.invoices.show', invoice.id)" class="text-indigo-600 hover:text-indigo-900">
                        Voir
                      </Link>
                      <a :href="route('customer.invoices.pdf', invoice.id)" target="_blank" class="text-green-600 hover:text-green-900">
                        PDF
                      </a>
                      <Link
                        v-if="['pending', 'overdue'].includes(invoice.status)"
                        :href="route('customer.invoices.pay', invoice.id)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        Payer
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Summary -->
              <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-3 gap-4 text-center">
                  <div>
                    <div class="text-sm text-gray-500">Total en attente</div>
                    <div class="text-2xl font-bold text-yellow-600">
                      {{ formatCurrency(pendingTotal) }}
                    </div>
                  </div>
                  <div>
                    <div class="text-sm text-gray-500">Total en retard</div>
                    <div class="text-2xl font-bold text-red-600">
                      {{ formatCurrency(overdueTotal) }}
                    </div>
                  </div>
                  <div>
                    <div class="text-sm text-gray-500">Total payé (cette année)</div>
                    <div class="text-2xl font-bold text-green-600">
                      {{ formatCurrency(paidTotal) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune facture</h3>
              <p class="mt-1 text-sm text-gray-500">
                Aucune facture ne correspond à votre recherche.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
  invoices: Array,
});

const statusFilter = ref('');

const filteredInvoices = computed(() => {
  if (!statusFilter.value) return props.invoices;
  return props.invoices.filter(i => i.status === statusFilter.value);
});

const pendingTotal = computed(() => {
  return props.invoices
    .filter(i => i.status === 'pending')
    .reduce((sum, i) => sum + parseFloat(i.total_ttc), 0);
});

const overdueTotal = computed(() => {
  return props.invoices
    .filter(i => i.status === 'overdue')
    .reduce((sum, i) => sum + parseFloat(i.total_ttc), 0);
});

const paidTotal = computed(() => {
  return props.invoices
    .filter(i => i.status === 'paid')
    .reduce((sum, i) => sum + parseFloat(i.total_ttc), 0);
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR');
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
  }).format(amount);
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800',
    paid: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800',
    overdue: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800',
    cancelled: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800',
  };
  return classes[status] || classes.pending;
};

const getStatusLabel = (status) => {
  const labels = {
    pending: 'En attente',
    paid: 'Payée',
    overdue: 'En retard',
    cancelled: 'Annulée',
  };
  return labels[status] || status;
};
</script>
