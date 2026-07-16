<template>
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 flex flex-col h-full overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Receive Goods (GRN Workspace)
            </h3>
        </div>

        <!-- Unsaved Draft Banner -->
        <div v-if="hasDraft" class="bg-amber-50 border-b border-amber-200 px-6 py-3 flex items-center justify-between animate-fadeIn shrink-0">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span class="text-sm font-bold text-amber-900">You have an unsaved GRN draft from a previous session.</span>
            </div>
            <div class="flex gap-2">
                <button @click="discardDraft" class="px-3 py-1.5 text-xs font-bold text-amber-700 bg-amber-100 hover:bg-amber-200 rounded-lg transition-colors">Discard</button>
                <button @click="resumeDraft" class="px-3 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-sm shadow-amber-600/20 rounded-lg transition-colors">Resume Draft</button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- Supplier, PO, and Invoicing details -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Supplier *</label>
                    <select v-model="form.supplier_id" @change="onSupplierChange" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none text-sm bg-white">
                        <option value="" disabled>Select Supplier</option>
                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                            {{ supplier.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Link Purchase Order (PO)</label>
                    <select v-model="selectedPoId" @change="loadPoDetails" :disabled="!form.supplier_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none text-sm bg-white">
                        <option :value="null">No PO / Direct Shipment</option>
                        <option v-for="po in filteredPos" :key="po.id" :value="po.id">
                            PO #{{ po.id }} - Total: ${{ parseFloat(po.total_amount).toFixed(2) }} ({{ po.status }})
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Supplier Invoice Number</label>
                    <input type="text" v-model="form.supplier_invoice_no" placeholder="e.g. SINV-10029" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none text-sm">
                </div>
            </div>

            <!-- Landed Cost Allocation Fields -->
            <div class="bg-teal-50/30 p-4 rounded-2xl border border-teal-100/50 grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">Freight Charges ($)</label>
                    <input type="number" step="0.01" v-model="form.freight_charges" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-teal-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">Other Landed Fees ($)</label>
                    <input type="number" step="0.01" v-model="form.other_charges" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-teal-500 outline-none text-sm">
                </div>
                <div class="flex flex-col justify-end">
                    <span class="text-xs text-slate-500 mb-1">Estimated landed cost will be allocated proportionally by value.</span>
                </div>
            </div>

            <!-- Item selection search -->
            <div v-if="!selectedPoId" class="border-t border-slate-100 pt-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Add Non-PO Item</label>
                <div class="relative">
                    <input 
                        type="text" 
                        v-model="searchQuery" 
                        @input="debouncedSearch"
                        placeholder="Search medicines in catalog..." 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none text-sm bg-slate-50"
                    >
                    <div v-if="searchResults.length > 0" class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                        <div 
                            v-for="med in searchResults" 
                            :key="med.id" 
                            @click="addItem(med)"
                            class="px-4 py-3 hover:bg-teal-50 cursor-pointer border-b border-slate-100 last:border-0"
                        >
                            <div class="font-medium text-slate-800">{{ med.name }}</div>
                            <div class="text-slate-500 text-xs">{{ med.generic_name }} • Strength: {{ med.strength }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="border-t border-slate-100 pt-4 space-y-4">
                <h4 class="font-bold text-slate-800">Shipment Line Details</h4>
                
                <div class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-100/50 text-[10px] uppercase text-slate-500 font-semibold">
                            <tr>
                                <th class="px-4 py-3 text-left">Medicine</th>
                                <th class="px-4 py-3 text-left">PO Qty</th>
                                <th class="px-4 py-3 text-left">Rec. Qty *</th>
                                <th class="px-4 py-3 text-left">Free Qty</th>
                                <th class="px-4 py-3 text-left">Unit Cost ($) *</th>
                                <th class="px-4 py-3 text-left">Sale Price ($) *</th>
                                <th class="px-4 py-3 text-left">Batch No. *</th>
                                <th class="px-4 py-3 text-left">Expiry *</th>
                                <th class="px-4 py-3 text-left">QC Status</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm">
                            <tr v-if="form.items.length === 0">
                                <td colspan="10" class="px-4 py-8 text-center text-slate-400">No items added to this GRN yet.</td>
                            </tr>
                            <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ item.name }}
                                </td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ item.ordered_qty || 0 }}
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" v-model="item.received_qty" min="0" class="w-16 px-2 py-1 rounded border border-slate-200 outline-none text-xs">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" v-model="item.free_qty" min="0" class="w-16 px-2 py-1 rounded border border-slate-200 outline-none text-xs">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" v-model="item.unit_cost" class="w-20 px-2 py-1 rounded border border-slate-200 outline-none text-xs">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" v-model="item.selling_price" class="w-20 px-2 py-1 rounded border border-slate-200 outline-none text-xs">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" v-model="item.batch_no" class="w-24 px-2 py-1 rounded border border-slate-200 outline-none text-xs">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="date" v-model="item.expiry_date" class="w-28 px-2 py-1 rounded border border-slate-200 outline-none text-xs">
                                </td>
                                <td class="px-4 py-3">
                                    <select v-model="item.qc_status" class="px-2 py-1 rounded border border-slate-200 bg-white text-xs outline-none">
                                        <option value="Pass">Pass</option>
                                        <option value="Fail">Fail</option>
                                    </select>
                                    <input 
                                        v-if="item.qc_status === 'Fail'" 
                                        type="text" 
                                        v-model="item.rejection_reason" 
                                        placeholder="Reason for fail" 
                                        class="w-24 px-2 py-1 rounded border border-red-300 outline-none text-xs mt-1 block"
                                    >
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-700">
                                    ${{ (item.received_qty * item.unit_cost).toFixed(2) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="removeItem(index)" class="text-red-500 hover:text-red-700 p-1">✕</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end gap-6 text-sm text-slate-600 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div>PO Subtotal: <strong>${{ poSubtotal.toFixed(2) }}</strong></div>
                    <div>Variance: <strong :class="varianceAmount !== 0 ? 'text-amber-600' : 'text-slate-800'">${{ varianceAmount.toFixed(2) }}</strong></div>
                    <div class="text-lg font-bold text-slate-800">Grand Total: ${{ grandTotal.toFixed(2) }}</div>
                </div>
            </div>
        </div>

        <div class="p-5 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="override_expiry" v-model="overrideExpiry" class="rounded border-slate-200 focus:ring-teal-500">
                <label for="override_expiry" class="text-xs text-slate-500">Override Expiry Gate (under manager approval)</label>
            </div>
            <div class="flex gap-3">
                <button @click="resetForm" class="px-6 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium rounded-xl transition-colors">
                    Clear
                </button>
                <button 
                    @click="submitGRN"
                    :disabled="loading || form.items.length === 0 || !form.supplier_id"
                    class="px-8 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-xl shadow-sm transition-colors disabled:opacity-50 flex items-center gap-2"
                >
                    <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Process GRN Receipt
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const hasDraft = ref(false);
const draftData = ref(null);


const loading = ref(false);
const suppliers = ref([]);
const purchaseOrders = ref([]);
const selectedPoId = ref(null);
const searchQuery = ref('');
const searchResults = ref([]);
const overrideExpiry = ref(false);
let searchTimeout = null;

const form = reactive({
    supplier_id: '',
    supplier_invoice_no: '',
    freight_charges: 0,
    other_charges: 0,
    items: []
});

const poSubtotal = computed(() => {
    return form.items.reduce((total, item) => total + ((item.ordered_qty || 0) * item.unit_cost), 0);
});

const grandTotal = computed(() => {
    const sub = form.items.reduce((total, item) => total + (item.received_qty * item.unit_cost), 0);
    return sub + parseFloat(form.freight_charges || 0) + parseFloat(form.other_charges || 0);
});

const varianceAmount = computed(() => {
    return grandTotal.value - poSubtotal.value;
});

const filteredPos = computed(() => {
    return purchaseOrders.value.filter(po => po.supplier_id === form.supplier_id);
});

const fetchSuppliers = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/suppliers');
        suppliers.value = res.data.data;
    } catch(e) { console.error(e); }
};

const fetchPurchaseOrders = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/purchases');
        purchaseOrders.value = res.data.data;
    } catch(e) { console.error(e); }
};

onMounted(() => {
    fetchSuppliers();
    fetchPurchaseOrders();
    
    // Check for draft
    const savedDraft = localStorage.getItem('grn_draft_state');
    if (savedDraft) {
        try {
            draftData.value = JSON.parse(savedDraft);
            hasDraft.value = true;
        } catch (e) {
            console.error('Failed to parse GRN draft', e);
            localStorage.removeItem('grn_draft_state');
        }
    }
});

const resumeDraft = () => {
    if (draftData.value) {
        selectedPoId.value = draftData.value.selectedPoId;
        Object.assign(form, draftData.value.form);
    }
    hasDraft.value = false;
};

const discardDraft = () => {
    localStorage.removeItem('grn_draft_state');
    draftData.value = null;
    hasDraft.value = false;
};

// Auto-save watch
watch(
    () => ({ selectedPoId: selectedPoId.value, form }),
    (newVal) => {
        if (!hasDraft.value && (newVal.form.supplier_id || newVal.form.items.length > 0)) {
            localStorage.setItem('grn_draft_state', JSON.stringify(newVal));
        }
    },
    { deep: true }
);

const onSupplierChange = () => {
    selectedPoId.value = null;
    form.items = [];
};

const loadPoDetails = async () => {
    if (!selectedPoId.value) {
        form.items = [];
        return;
    }

    try {
        const res = await axios.get(`/api/v1/pharmacy/grn/po/${selectedPoId.value}`);
        const po = res.data.data;
        form.items = po.items.map(item => ({
            medicine_id: item.medicine_id,
            purchase_order_item_id: item.id,
            name: item.medicine.name,
            ordered_qty: item.quantity,
            received_qty: item.quantity,
            free_qty: 0,
            unit_cost: parseFloat(item.unit_cost),
            selling_price: parseFloat(item.medicine.selling_price || item.unit_cost * 1.2),
            batch_no: item.batch_number || `BCH-${Math.floor(1000 + Math.random() * 9000)}`,
            expiry_date: item.expiry_date || '',
            qc_status: 'Pass',
            rejection_reason: ''
        }));
    } catch(e) {
        alert('Failed to load purchase order details.');
    }
};

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    if(searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }
    searchTimeout = setTimeout(async () => {
        try {
            const res = await axios.get(`/api/v1/pharmacy/medicines?q=${searchQuery.value}`);
            searchResults.value = res.data.data;
        } catch(e) { console.error(e); }
    }, 300);
};

const addItem = (med) => {
    form.items.push({
        medicine_id: med.id,
        name: med.name,
        ordered_qty: 0,
        received_qty: 10,
        free_qty: 0,
        unit_cost: parseFloat(med.purchase_price || 0),
        selling_price: parseFloat(med.selling_price || 0),
        batch_no: `BCH-${Math.floor(1000 + Math.random() * 9000)}`,
        expiry_date: '',
        qc_status: 'Pass',
        rejection_reason: ''
    });
    searchQuery.value = '';
    searchResults.value = [];
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const resetForm = () => {
    form.supplier_id = '';
    form.supplier_invoice_no = '';
    form.freight_charges = 0;
    form.other_charges = 0;
    form.items = [];
    selectedPoId.value = null;
};

const submitGRN = async () => {
    loading.value = true;
    try {
        const payload = {
            ...form,
            purchase_order_id: selectedPoId.value,
            override_expiry: overrideExpiry.value
        };

        const res = await axios.post(`/api/v1/pharmacy/grn`, payload);
        alert(res.data.message);
        localStorage.removeItem('grn_draft_state');
        resetForm();
    } catch(e) {
        alert(e.response?.data?.error || e.response?.data?.message || "Error processing GRN shipment.");
        console.error(e);
    } finally {
        loading.value = false;
    }
};
</script>
