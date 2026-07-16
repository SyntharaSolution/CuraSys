<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
        
        <!-- Modal -->
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-lg relative z-10 overflow-hidden flex flex-col max-h-[95vh] transform transition-all">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xl font-bold text-slate-800">{{ isPending ? 'Send to Cashier (Pending)' : 'Direct Checkout' }}</h3>
                <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 bg-white p-1 rounded-full shadow-sm hover:shadow transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-5 overflow-y-auto">
                <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-100/50 flex justify-between items-center">
                    <span class="text-blue-800 font-medium">Cart Subtotal</span>
                    <span class="text-2xl font-bold text-blue-900">${{ cartTotal.toFixed(2) }}</span>
                </div>

                <!-- Customer selection -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Link Customer (Required for A/R or Loyalty)</label>
                    <select v-model="customerId" @change="onCustomerChange" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option :value="null">Walk-in Patient (Cash/Card only)</option>
                        <option v-for="cust in customers" :key="cust.id" :value="cust.id">
                            {{ cust.name }} ({{ cust.phone || 'No phone' }} - {{ cust.customer_type }})
                        </option>
                    </select>
                    <div v-if="selectedCustomer" class="text-xs text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-100 flex justify-between">
                        <span>Loyalty Bal: <strong>{{ selectedCustomer.loyalty_points_balance }} pts</strong></span>
                        <span>Credit Limit: <strong>${{ parseFloat(selectedCustomer.credit_limit).toFixed(2) }}</strong></span>
                    </div>
                </div>

                <!-- Pharmacist digital verification for controlled drugs -->
                <div v-if="hasControlledDrugs" class="bg-amber-50 border border-amber-200/50 p-4 rounded-xl space-y-3">
                    <div class="flex items-center gap-2 text-amber-800 font-semibold text-sm">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Prescription-Only Medicine Detected
                    </div>
                    <p class="text-xs text-amber-700">This order contains controlled substances. A digital stamp from a Pharmacist is required to proceed.</p>
                    <div class="flex gap-2">
                        <select v-model="verifiedByPharmacist" class="flex-1 px-3 py-1.5 rounded-lg border border-amber-300 bg-white text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                            <option :value="null">Select Pharmacist...</option>
                            <option v-for="pharmacist in pharmacists" :key="pharmacist.id" :value="pharmacist.id">
                                {{ pharmacist.name }} (Pharmacist)
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Discounts & Manager Pin Override -->
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Discount Amount ($)</label>
                            <input 
                                type="number" 
                                v-model="discount" 
                                @input="checkDiscountApproval"
                                min="0"
                                :max="cartTotal"
                                class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Net Payable</label>
                            <div class="px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 font-bold text-slate-800 text-lg">
                                ${{ netTotal.toFixed(2) }}
                            </div>
                        </div>
                    </div>

                    <!-- Manager PIN override panel -->
                    <div v-if="requiresManagerPin" class="bg-red-50 border border-red-200/50 p-4 rounded-xl space-y-2">
                        <label class="block text-xs font-bold text-red-800 uppercase tracking-wider">Manager PIN Override Required (>15% discount)</label>
                        <input 
                            type="password" 
                            v-model="managerPin" 
                            placeholder="Enter 4-Digit Manager PIN" 
                            class="w-full px-4 py-2 rounded-xl border border-red-300 focus:ring-2 focus:ring-red-500 outline-none text-center tracking-widest text-lg bg-white"
                        >
                    </div>
                </div>

                <!-- Multiple & Split Payments Configuration -->
                <div v-if="!isPending" class="space-y-3 border-t border-slate-100 pt-4">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-semibold text-slate-700">Payment Breakdown</label>
                        <button type="button" @click="addPaymentMethod" class="text-xs text-blue-600 hover:text-blue-800 font-bold">+ Add Split Method</button>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(pay, index) in paymentRows" :key="index" class="flex gap-2 items-center bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <select v-model="pay.method" class="w-1/3 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs outline-none">
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Insurance Claim" :disabled="!customerId">Insurance Claim</option>
                                <option value="Store Credit" :disabled="!customerId">Store Credit</option>
                            </select>
                            
                            <input 
                                type="number" 
                                step="0.01" 
                                v-model="pay.amount" 
                                placeholder="Amount" 
                                class="w-1/4 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs outline-none"
                            >
                            
                            <input 
                                type="text" 
                                v-model="pay.reference_no" 
                                placeholder="Ref/Auth code" 
                                class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs outline-none"
                            >

                            <button @click="removePaymentMethod(index)" class="text-red-500 hover:text-red-700 text-xs font-bold px-2">✕</button>
                        </div>
                    </div>
                </div>

                <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm border border-red-100 flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ error }}
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-slate-100 bg-slate-50/50">
                <button 
                    @click="processCheckout" 
                    :disabled="loading || (hasControlledDrugs && !verifiedByPharmacist) || (requiresManagerPin && !managerPin)"
                    class="w-full text-white font-semibold py-3.5 px-4 rounded-xl shadow-lg shadow-blue-600/20 bg-blue-600 hover:bg-blue-700 transition-all flex justify-center items-center gap-2"
                >
                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Confirm Checkout & Print Invoice
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    cartTotal: {
        type: Number,
        required: true
    },
    items: {
        type: Array,
        required: true
    },
    isPending: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'process']);

const customers = ref([]);
const pharmacists = ref([]);
const customerId = ref(null);
const selectedCustomer = ref(null);
const discount = ref(0);
const verifiedByPharmacist = ref(null);
const managerPin = ref('');
const requiresManagerPin = ref(false);
const error = ref('');
const loading = ref(false);

const paymentRows = ref([
    { method: 'Cash', amount: props.cartTotal, reference_no: '' }
]);

const netTotal = computed(() => {
    return Math.max(0, props.cartTotal - (Number(discount.value) || 0));
});

// Check if any cart items are prescription/controlled category
const hasControlledDrugs = computed(() => {
    // Check category_id or reorder_level as indicator
    return props.items.some(item => item.category_id === 2 || item.controlled === true);
});

onMounted(async () => {
    try {
        const cRes = await axios.get('/api/v1/pharmacy/customers');
        customers.value = cRes.data.data;
        
        // Simulating getting pharmacists list
        pharmacists.value = [
            { id: 2, name: 'Dr. Jane Smith' },
            { id: 3, name: 'Sarah Connor' }
        ];
    } catch (e) {
        console.error("Failed to load customer list for POS checkout", e);
    }
});

const onCustomerChange = () => {
    selectedCustomer.value = customers.value.find(c => c.id === customerId.value) || null;
};

const checkDiscountApproval = () => {
    const discountPercent = props.cartTotal > 0 ? (Number(discount.value) / props.cartTotal) * 100 : 0;
    requiresManagerPin.value = discountPercent > 15.0;
};

const addPaymentMethod = () => {
    paymentRows.value.push({ method: 'Card', amount: 0, reference_no: '' });
};

const removePaymentMethod = (index) => {
    paymentRows.value.splice(index, 1);
};

const processCheckout = () => {
    // Validate payments match total
    if (!props.isPending) {
        const sum = paymentRows.value.reduce((s, p) => s + parseFloat(p.amount || 0), 0);
        if (Math.abs(sum - netTotal.value) > 0.01) {
            error.value = `Total payments ($${sum.toFixed(2)}) must match net payable ($${netTotal.value.toFixed(2)})`;
            return;
        }
    }

    loading.value = true;
    error.value = '';

    const payload = {
        customer_id: customerId.value,
        discount_amount: Number(discount.value) || 0,
        payments: props.isPending ? [{ method: 'Cash', amount: netTotal.value }] : paymentRows.value,
        verified_by_pharmacist: verifiedByPharmacist.value,
        manager_pin: managerPin.value
    };

    emit('process', payload);
};
</script>
