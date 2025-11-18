<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Modifier le contrat {{ contract.contract_number }}</h1>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">TVA (%) *</label>
                        <input
                            v-model="form.tax_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.tax_rate }"
                            required
                        />
                        <div v-if="form.errors.tax_rate" class="text-red-600 text-sm mt-1">{{ form.errors.tax_rate }}</div>
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
                        <div class="font-bold text-lg text-right border-t pt-2">{{ formatCurrency(calculateTotal()) }}</div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('contracts.show', contract.id)" class="btn btn-secondary">
                        Annuler
                    </Link>
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        Mettre à jour
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
    contract: Object,
    customers: Array,
});

const form = useForm({
    price_monthly_ht: props.contract.price_monthly_ht,
    tax_rate: props.contract.tax_rate,
    deposit_amount: props.contract.deposit_amount,
    payment_method: props.contract.payment_method,
    payment_day: props.contract.payment_day,
    insurance_monthly: props.contract.insurance_monthly,
    notes: props.contract.notes,
});

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
    form.put(route('contracts.update', props.contract.id));
};
</script>
