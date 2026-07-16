<template>
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Purchase Invoices</h1>
                <p class="text-sm text-slate-500 mt-1">Review all past Goods Receipt Notes (GRN) and invoices.</p>
            </div>
            <router-link to="/app/purchasing/new" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-xl shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New GRN
            </router-link>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Ref No.</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr v-if="loading">
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">Loading invoices...</td>
                        </tr>
                        <tr v-else-if="purchases.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                No purchase invoices found.
                            </td>
                        </tr>
                        <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ new Date(purchase.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                {{ purchase.reference_number || 'N/A' }}
                                <div class="text-xs text-slate-400 font-normal">ID: {{ purchase.uuid.substring(0,8) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                {{ getSupplierName(purchase.supplier_id) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ purchase.status.charAt(0).toUpperCase() + purchase.status.slice(1) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ purchase.items?.length || 0 }} products
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 text-right">
                                ${{ parseFloat(purchase.total_amount).toFixed(2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const purchases = ref([]);
const suppliers = ref([]);
const loading = ref(true);

const fetchData = async () => {
    loading.value = true;
    try {
        const [purchasesRes, suppliersRes] = await Promise.all([
            axios.get('/api/v1/pharmacy/purchases'),
            axios.get('/api/v1/pharmacy/suppliers')
        ]);
        
        purchases.value = purchasesRes.data.data;
        suppliers.value = suppliersRes.data.data;
    } catch (e) {
        console.error("Failed to fetch purchases", e);
    } finally {
        loading.value = false;
    }
};

const getSupplierName = (id) => {
    const s = suppliers.value.find(sup => sup.id === id);
    return s ? s.name : 'Unknown Supplier';
};

onMounted(() => {
    fetchData();
});
</script>
