<template>
  <CustomerLayout title="Mes Contrats">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-gray-900">Mes Contrats</h2>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex gap-4">
              <select v-model="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tous les statuts</option>
                <option value="active">Actifs</option>
                <option value="pending">En attente</option>
                <option value="terminated">Résiliés</option>
              </select>
            </div>

            <!-- Contracts List -->
            <div v-if="filteredContracts.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Numéro
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Box
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Site
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Période
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Prix/Mois
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
                  <tr v-for="contract in filteredContracts" :key="contract.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ contract.contract_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ contract.box.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ contract.box.floor.building.site.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(contract.start_date) }} - {{ formatDate(contract.end_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                      {{ formatCurrency(contract.price_monthly_ttc) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusClass(contract.status)">
                        {{ getStatusLabel(contract.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                      <Link :href="route('customer.contracts.show', contract.id)" class="text-indigo-600 hover:text-indigo-900">
                        Voir
                      </Link>
                      <a :href="route('customer.contracts.pdf', contract.id)" target="_blank" class="text-green-600 hover:text-green-900">
                        PDF
                      </a>
                      <button
                        v-if="contract.status === 'active'"
                        @click="requestTermination(contract.id)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Résilier
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun contrat</h3>
              <p class="mt-1 text-sm text-gray-500">
                Aucun contrat ne correspond à votre recherche.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Termination Modal -->
    <Modal :show="showTerminationModal" @close="showTerminationModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">
          Demande de Résiliation
        </h2>
        <form @submit.prevent="submitTermination">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Raison</label>
            <select v-model="terminationForm.reason" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Sélectionnez une raison</option>
              <option value="moving">Déménagement</option>
              <option value="too_expensive">Trop cher</option>
              <option value="no_longer_needed">Plus besoin</option>
              <option value="found_alternative">Trouvé une alternative</option>
              <option value="other">Autre</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Commentaires</label>
            <textarea v-model="terminationForm.comments" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin souhaitée</label>
            <input type="date" v-model="terminationForm.preferred_end_date" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
          </div>
          <div class="flex justify-end gap-3">
            <button type="button" @click="showTerminationModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
              Annuler
            </button>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
              Confirmer la résiliation
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  contracts: Array,
});

const statusFilter = ref('');
const showTerminationModal = ref(false);
const selectedContractId = ref(null);

const terminationForm = useForm({
  reason: '',
  comments: '',
  preferred_end_date: '',
});

const filteredContracts = computed(() => {
  if (!statusFilter.value) return props.contracts;
  return props.contracts.filter(c => c.status === statusFilter.value);
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
    active: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800',
    pending: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800',
    terminated: 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800',
  };
  return classes[status] || classes.pending;
};

const getStatusLabel = (status) => {
  const labels = {
    active: 'Actif',
    pending: 'En attente',
    terminated: 'Résilié',
  };
  return labels[status] || status;
};

const requestTermination = (contractId) => {
  selectedContractId.value = contractId;
  showTerminationModal.value = true;
};

const submitTermination = () => {
  terminationForm.post(route('customer.contracts.terminate', selectedContractId.value), {
    onSuccess: () => {
      showTerminationModal.value = false;
      terminationForm.reset();
    },
  });
};
</script>
