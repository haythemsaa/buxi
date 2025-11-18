<template>
    <AppLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">Vue d'ensemble de votre activité</p>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Occupancy Rate -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-indigo-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Taux d'occupation</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ kpis.occupancyRate }}%</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <span class="font-medium text-gray-900">{{ kpis.occupiedBoxes }}</span>
                        <span class="text-gray-600"> / {{ kpis.totalBoxes }} boxes occupées</span>
                    </div>
                </div>
            </div>

            <!-- Active Contracts -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-green-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Contrats actifs</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ kpis.activeContracts }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <span class="font-medium text-gray-900">{{ kpis.activeCustomers }}</span>
                        <span class="text-gray-600"> clients actifs</span>
                    </div>
                </div>
            </div>

            <!-- Revenue -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-yellow-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Revenus du mois</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ formatCurrency(kpis.currentMonthRevenue) }}</div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold" :class="kpis.revenueChange >= 0 ? 'text-green-600' : 'text-red-600'">
                                        <span>{{ kpis.revenueChange >= 0 ? '+' : '' }}{{ kpis.revenueChange }}%</span>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm text-gray-600">
                        Mois dernier: {{ formatCurrency(kpis.lastMonthRevenue) }}
                    </div>
                </div>
            </div>

            <!-- Pending Invoices -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-red-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Impayés</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ formatCurrency(kpis.overdueAmount) }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm text-gray-600">
                        En attente: {{ formatCurrency(kpis.pendingAmount) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Revenue Chart -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Revenus mensuels</h3>
                <div class="h-64">
                    <canvas ref="revenueChart"></canvas>
                </div>
            </div>

            <!-- Occupancy Chart -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Évolution du taux d'occupation</h3>
                <div class="h-64">
                    <canvas ref="occupancyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Contracts -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Contrats récents</h3>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="contract in recentContracts" :key="contract.id" class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ contract.customer?.full_name || 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Box {{ contract.box?.number }} - {{ formatCurrency(contract.monthly_price) }}/mois
                                </p>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getStatusClass(contract.status)">
                                    {{ getStatusLabel(contract.status) }}
                                </span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Overdue Invoices -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Factures en retard</h3>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="invoice in overdueInvoices" :key="invoice.id" class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ invoice.customer?.full_name || 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ invoice.invoice_number }} - Échéance: {{ formatDate(invoice.due_date) }}
                                </p>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <span class="text-sm font-medium text-red-600">
                                    {{ formatCurrency(invoice.total_ttc) }}
                                </span>
                            </div>
                        </div>
                    </li>
                    <li v-if="overdueInvoices.length === 0" class="px-6 py-4 text-center text-sm text-gray-500">
                        Aucune facture en retard
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    kpis: Object,
    sites: Array,
    recentContracts: Array,
    pendingInvoices: Array,
    overdueInvoices: Array,
    monthlyRevenue: Array,
    occupancyTrend: Array,
});

const revenueChart = ref(null);
const occupancyChart = ref(null);

onMounted(() => {
    // Create Revenue Chart
    new Chart(revenueChart.value, {
        type: 'bar',
        data: {
            labels: props.monthlyRevenue.map(item => item.month),
            datasets: [{
                label: 'Revenus (€)',
                data: props.monthlyRevenue.map(item => item.revenue),
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                }
            }
        }
    });

    // Create Occupancy Chart
    new Chart(occupancyChart.value, {
        type: 'line',
        data: {
            labels: props.occupancyTrend.map(item => item.month),
            datasets: [{
                label: 'Taux d\'occupation (%)',
                data: props.occupancyTrend.map(item => item.rate),
                borderColor: 'rgba(34, 197, 94, 1)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                }
            }
        }
    });
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR');
};

const getStatusClass = (status) => {
    const classes = {
        'active': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'suspended': 'bg-red-100 text-red-800',
        'terminated': 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        'active': 'Actif',
        'pending': 'En attente',
        'suspended': 'Suspendu',
        'terminated': 'Terminé',
    };
    return labels[status] || status;
};
</script>
