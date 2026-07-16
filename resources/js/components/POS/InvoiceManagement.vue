<template>
    <div class="h-full flex flex-col w-full p-6 space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Invoices & Sales History</h1>
                <p class="text-slate-500 mt-1">View and reprint past sales.</p>
            </div>
            
            <div class="flex gap-4">
                <input 
                    type="text" 
                    placeholder="Search invoice number..." 
                    v-model="searchQuery"
                    class="px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64 bg-white/70 backdrop-blur-sm"
                >
            </div>
        </div>

        <div class="flex-1 bg-white/70 backdrop-blur-xl rounded-3xl shadow-sm border border-white/80 overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-sm border-b border-slate-100">
                            <th class="py-4 px-6 font-medium">Invoice Number</th>
                            <th class="py-4 px-6 font-medium">Date & Time</th>
                            <th class="py-4 px-6 font-medium">Customer</th>
                            <th class="py-4 px-6 font-medium">Total Amount</th>
                            <th class="py-4 px-6 font-medium">Payment</th>
                            <th class="py-4 px-6 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="loading" class="text-center">
                            <td colspan="6" class="py-12 text-slate-400">Loading invoices...</td>
                        </tr>
                        <tr v-else-if="filteredInvoices.length === 0" class="text-center">
                            <td colspan="6" class="py-12 text-slate-400">No invoices found.</td>
                        </tr>
                        <tr 
                            v-else
                            v-for="invoice in filteredInvoices" 
                            :key="invoice.uuid" 
                            class="hover:bg-slate-50/50 transition-colors"
                        >
                            <td class="py-4 px-6 font-medium text-slate-800">
                                {{ invoice.invoice_number || invoice.uuid.substring(0, 8).toUpperCase() }}
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ new Date(invoice.created_at).toLocaleString() }}
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ invoice.patient_id ? `Customer ID: ${invoice.patient_id}` : 'Walk-in' }}
                            </td>
                            <td class="py-4 px-6 font-medium text-slate-800">
                                ${{ parseFloat(invoice.net_amount).toFixed(2) }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2 py-1 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">
                                    {{ invoice.payment_method }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button 
                                    @click="printInvoice(invoice)"
                                    class="text-indigo-600 hover:text-indigo-800 font-medium text-sm transition-colors"
                                >
                                    Print Receipt
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const invoices = ref([]);
const loading = ref(true);
const searchQuery = ref('');

const fetchInvoices = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/invoices');
        invoices.value = res.data.data;
    } catch (e) {
        console.error("Failed to load invoices", e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchInvoices();
});

const filteredInvoices = computed(() => {
    if (!searchQuery.value) return invoices.value;
    const q = searchQuery.value.toLowerCase();
    return invoices.value.filter(inv => {
        const invNum = inv.invoice_number ? inv.invoice_number.toLowerCase() : '';
        const uuid = inv.uuid.toLowerCase();
        return invNum.includes(q) || uuid.includes(q);
    });
});

const printInvoice = (invoice) => {
    alert(`Printing invoice ${invoice.invoice_number || invoice.uuid}...\nIn a real app, this would open a printable PDF window.`);
};
</script>
