<template>
    <div class="h-full flex flex-col bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden p-6 space-y-6">
        <div>
            <h3 class="text-xl font-bold text-slate-800 tracking-tight">Supplier Bills Payout</h3>
            <p class="text-slate-500 text-xs mt-1">Review approved Supplier Invoices and post payments directly from your cash register shift drawer.</p>
        </div>

        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase border-b border-slate-100">
                        <th class="py-3 px-4">GRN No.</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Invoice Ref</th>
                        <th class="py-3 px-4">Date Approved</th>
                        <th class="py-3 px-4 text-right">Grand Total ($)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <tr v-if="loading" class="text-center">
                        <td colspan="7" class="py-12 text-slate-400">Loading supplier invoices...</td>
                    </tr>
                    <tr v-else-if="bills.length === 0" class="text-center">
                        <td colspan="7" class="py-12 text-slate-400">No approved supplier bills found.</td>
                    </tr>
                    <tr v-else v-for="bill in bills" :key="bill.id" class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-700">
                            {{ bill.grn_no }}
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">
                            {{ bill.supplier?.name }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 font-mono text-xs">
                            {{ bill.supplier_invoice_no || 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ new Date(bill.approved_at).toLocaleDateString() }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-800">
                            ${{ parseFloat(bill.grand_total).toFixed(2) }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span 
                                :class="[
                                    'px-2.5 py-1 rounded-full text-xs font-bold',
                                    bill.payment_status === 'Paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'
                                ]"
                            >
                                {{ bill.payment_status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <button 
                                v-if="bill.payment_status === 'Unpaid'"
                                @click="payBill(bill)"
                                :disabled="payingId === bill.id"
                                class="px-4 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold transition-colors disabled:opacity-50"
                            >
                                {{ payingId === bill.id ? 'Processing...' : 'Pay from Drawer' }}
                            </button>
                            <span v-else class="text-xs text-slate-400 font-medium">Fully Settled</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const bills = ref([]);
const loading = ref(true);
const payingId = ref(null);

const fetchBills = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/supplier-bills');
        bills.value = res.data.data;
    } catch(e) {
        console.error("Failed to fetch supplier bills", e);
    } finally {
        loading.value = false;
    }
};

const payBill = async (bill) => {
    if (!confirm(`Are you sure you want to pay $${parseFloat(bill.grand_total).toFixed(2)} to ${bill.supplier?.name} from your register drawer? This records a cashier payout movement.`)) {
        return;
    }

    payingId.value = bill.id;
    try {
        await axios.post(`/api/v1/pharmacy/pos/supplier-bills/${bill.id}/pay`);
        alert('Supplier bill paid successfully. Cash drawer payout recorded.');
        fetchBills();
    } catch (e) {
        alert(e.response?.data?.message || 'Payment processing failed.');
    } finally {
        payingId.value = null;
    }
};

onMounted(() => {
    fetchBills();
});
</script>
