<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Créer un nouveau contrat</h1>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Client *</label>
                        <select
                            v-model="form.customer_id"
                            class="form-select"
                            :class="{ 'is-invalid': form.errors.customer_id }"
                            required
                        >
                            <option value="">Sélectionner un client</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.type === 'professional' ? customer.company_name : `${customer.first_name} ${customer.last_name}` }}
                                - {{ customer.customer_number }}
                            </option>
                        </select>
                        <div v-if="form.errors.customer_id" class="text-red-600 text-sm mt-1">{{ form.errors.customer_id }}</div>
                    </div>

                    <!-- Box Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Box *</label>
                        <select
                            v-model="form.box_id"
                            class="form-select"
                            :class="{ 'is-invalid': form.errors.box_id }"
                            required
                            @change="updateBoxPrice"
                        >
                            <option value="">Sélectionner un box</option>
                            <option v-for="box in availableBoxes" :key="box.id" :value="box.id">
                                Box {{ box.number }} - {{ Math.round(box.volume) }}m³
                                ({{ formatCurrency(box.current_price_monthly) }}/mois)
                            </option>
                        </select>
                        <div v-if="form.errors.box_id" class="text-red-600 text-sm mt-1">{{ form.errors.box_id }}</div>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de début *</label>
                        <input
                            v-model="form.start_date"
                            type="date"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.start_date }"
                            required
                        />
                        <div v-if="form.errors.start_date" class="text-red-600 text-sm mt-1">{{ form.errors.start_date }}</div>
                    </div>

                    <!-- Initial Duration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Durée initiale (mois)</label>
                        <input
                            v-model="form.initial_duration_months"
                            type="number"
                            min="1"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.initial_duration_months }"
                        />
                        <div v-if="form.errors.initial_duration_months" class="text-red-600 text-sm mt-1">
                            {{ form.errors.initial_duration_months }}
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prix mensuel HT (€) *</label>
                        <input
                            v-model="form.price_monthly_ht"
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.price_monthly_ht }"
                            required
                        />
                        <div v-if="form.errors.price_monthly_ht" class="text-red-600 text-sm mt-1">{{ form.errors.price_monthly_ht }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">TVA (%)</label>
                        <input
                            v-model="form.tax_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.tax_rate }"
                        />
                        <div v-if="form.errors.tax_rate" class="text-red-600 text-sm mt-1">{{ form.errors.tax_rate }}</div>
                    </div>

                    <!-- Deposit -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dépôt de garantie (€)</label>
                        <input
                            v-model="form.deposit_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.deposit_amount }"
                        />
                        <div v-if="form.errors.deposit_amount" class="text-red-600 text-sm mt-1">{{ form.errors.deposit_amount }}</div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mode de paiement *</label>
                        <select
                            v-model="form.payment_method"
                            class="form-select"
                            :class="{ 'is-invalid': form.errors.payment_method }"
                            required
                        >
                            <option value="">Sélectionner</option>
                            <option value="sepa">Prélèvement SEPA</option>
                            <option value="card">Carte bancaire</option>
                            <option value="transfer">Virement bancaire</option>
                            <option value="cash">Espèces</option>
                            <option value="check">Chèque</option>
                        </select>
                        <div v-if="form.errors.payment_method" class="text-red-600 text-sm mt-1">{{ form.errors.payment_method }}</div>
                    </div>

                    <!-- Payment Day -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jour de prélèvement</label>
                        <input
                            v-model="form.payment_day"
                            type="number"
                            min="1"
                            max="28"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.payment_day }"
                        />
                        <div v-if="form.errors.payment_day" class="text-red-600 text-sm mt-1">{{ form.errors.payment_day }}</div>
                    </div>

                    <!-- Insurance -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assurance mensuelle (€)</label>
                        <input
                            v-model="form.insurance_monthly"
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.insurance_monthly }"
                        />
                        <div v-if="form.errors.insurance_monthly" class="text-red-600 text-sm mt-1">{{ form.errors.insurance_monthly }}</div>
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.notes }"
                        ></textarea>
                        <div v-if="form.errors.notes" class="text-red-600 text-sm mt-1">{{ form.errors.notes }}</div>
                    </div>
                </div>

                <!-- Summary -->
                <div v-if="form.price_monthly_ht" class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">Récapitulatif</h3>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>Prix mensuel HT:</div>
                        <div class="font-medium text-right">{{ formatCurrency(parseFloat(form.price_monthly_ht)) }}</div>

                        <div v-if="form.insurance_monthly">Assurance:</div>
                        <div v-if="form.insurance_monthly" class="font-medium text-right">
                            {{ formatCurrency(parseFloat(form.insurance_monthly)) }}
                        </div>

                        <div>TVA ({{ form.tax_rate }}%):</div>
                        <div class="font-medium text-right">{{ formatCurrency(calculateTax()) }}</div>

                        <div class="font-bold border-t pt-2">Total TTC mensuel:</div>
                        <div class="font-bold text-right border-t pt-2">{{ formatCurrency(calculateTotal()) }}</div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('contracts.index')" class="btn btn-secondary">
                        Annuler
                    </Link>
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        Créer le contrat
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    customers: Array,
    availableBoxes: Array,
    selectedBoxId: Number,
});

const form = useForm({
    customer_id: '',
    box_id: props.selectedBoxId || '',
    start_date: new Date().toISOString().split('T')[0],
    initial_duration_months: 12,
    price_monthly_ht: '',
    tax_rate: 20,
    deposit_amount: '',
    payment_method: 'sepa',
    payment_day: 5,
    insurance_monthly: 0,
    notes: '',
});

const updateBoxPrice = () => {
    const selectedBox = props.availableBoxes.find(box => box.id === parseInt(form.box_id));
    if (selectedBox) {
        form.price_monthly_ht = selectedBox.current_price_monthly;
    }
};

const calculateTax = () => {
    const ht = parseFloat(form.price_monthly_ht) || 0;
    const insurance = parseFloat(form.insurance_monthly) || 0;
    const taxRate = parseFloat(form.tax_rate) || 0;
    return ((ht + insurance) * taxRate) / 100;
};

const calculateTotal = () => {
    const ht = parseFloat(form.price_monthly_ht) || 0;
    const insurance = parseFloat(form.insurance_monthly) || 0;
    return ht + insurance + calculateTax();
};

const formatCurrency = (amount) => {
    if (!amount) return '0,00 €';
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount);
};

const submit = () => {
    form.post(route('contracts.store'));
};

// Initialize box price if box is pre-selected
if (props.selectedBoxId) {
    updateBoxPrice();
}
</script>
