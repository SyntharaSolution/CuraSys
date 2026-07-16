<template>
    <div class="h-full flex flex-col w-full p-6 space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Customer Management</h1>
                <p class="text-slate-500 mt-1">Manage pharmacy customers, loyalty points, and credit accounts.</p>
            </div>
            
            <button 
                @click="openAddModal"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-sm transition-colors font-medium flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Customer
            </button>
        </div>

        <div class="flex-1 bg-white/70 backdrop-blur-xl rounded-3xl shadow-sm border border-white/80 overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 bg-white/50 flex gap-4">
                <input 
                    v-model="search" 
                    @input="fetchCustomers"
                    placeholder="Search by name, phone or NIC..." 
                    class="flex-1 max-w-md px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                />
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-sm border-b border-slate-100">
                            <th class="py-4 px-6 font-medium">Code</th>
                            <th class="py-4 px-6 font-medium">Name</th>
                            <th class="py-4 px-6 font-medium">Phone</th>
                            <th class="py-4 px-6 font-medium">NIC/Passport</th>
                            <th class="py-4 px-6 font-medium">Type</th>
                            <th class="py-4 px-6 font-medium">Credit Limit</th>
                            <th class="py-4 px-6 font-medium">Loyalty Points</th>
                            <th class="py-4 px-6 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="loading" class="text-center">
                            <td colspan="8" class="py-12 text-slate-400">Loading customers...</td>
                        </tr>
                        <tr v-else-if="customers.length === 0" class="text-center">
                            <td colspan="8" class="py-12 text-slate-400">No customers found.</td>
                        </tr>
                        <tr 
                            v-else
                            v-for="customer in customers" 
                            :key="customer.id" 
                            class="hover:bg-slate-50/50 transition-colors"
                        >
                            <td class="py-4 px-6 font-mono text-xs text-slate-500">
                                {{ customer.customer_code }}
                            </td>
                            <td class="py-4 px-6 font-medium text-slate-800">
                                {{ customer.name }}
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ customer.phone || 'N/A' }}
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ customer.NIC_Passport || 'N/A' }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 capitalize">
                                {{ customer.customer_type.replace('_', ' ') }}
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                ${{ parseFloat(customer.credit_limit).toFixed(2) }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">
                                    {{ customer.loyalty_points_balance }} pts
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <button 
                                    @click="openLedger(customer)"
                                    class="text-teal-600 hover:text-teal-800 font-medium text-sm transition-colors"
                                >
                                    Ledger
                                </button>
                                <button 
                                    @click="openEditModal(customer)"
                                    class="text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ledger Statement Modal -->
        <div v-if="showLedgerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showLedgerModal = false"></div>
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-4xl relative z-10 p-6 flex flex-col max-h-[90vh]">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Customer Credit Ledger</h3>
                <p class="text-slate-500 mb-4">{{ selectedCustomer?.name }} (Code: {{ selectedCustomer?.customer_code }})</p>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="text-xs text-slate-400 font-medium uppercase">Outstanding Balance</div>
                        <div class="text-2xl font-bold text-slate-800 mt-1">${{ parseFloat(ledgerData.outstanding_balance || 0).toFixed(2) }}</div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="text-xs text-slate-400 font-medium uppercase">Credit Limit</div>
                        <div class="text-2xl font-bold text-slate-800 mt-1">${{ parseFloat(selectedCustomer?.credit_limit || 0).toFixed(2) }}</div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex items-center justify-between">
                        <button 
                            @click="showPayModal = true"
                            class="w-full py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-medium shadow-sm transition-colors"
                        >
                            Receive Payment
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase border-b border-slate-100">
                                <th class="py-2.5 px-4">Date</th>
                                <th class="py-2.5 px-4">Type</th>
                                <th class="py-2.5 px-4">Ref ID</th>
                                <th class="py-2.5 px-4">Debit ($)</th>
                                <th class="py-2.5 px-4">Credit ($)</th>
                                <th class="py-2.5 px-4">Balance ($)</th>
                                <th class="py-2.5 px-4">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-if="ledgerData.ledger.length === 0">
                                <td colspan="7" class="py-6 text-center text-slate-400">No ledger transactions found.</td>
                            </tr>
                            <tr v-for="entry in ledgerData.ledger" :key="entry.id">
                                <td class="py-3 px-4">{{ new Date(entry.transaction_date).toLocaleDateString() }}</td>
                                <td class="py-3 px-4 capitalize font-medium text-slate-700">{{ entry.transaction_type }}</td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-500">{{ entry.reference_id || 'N/A' }}</td>
                                <td class="py-3 px-4 text-red-600 font-medium">+{{ parseFloat(entry.debit).toFixed(2) }}</td>
                                <td class="py-3 px-4 text-green-600 font-medium">-{{ parseFloat(entry.credit).toFixed(2) }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ parseFloat(entry.balance).toFixed(2) }}</td>
                                <td class="py-3 px-4 text-slate-500 text-xs">{{ entry.notes || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button @click="showLedgerModal = false" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Receive Credit Payment Modal -->
        <div v-if="showPayModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showPayModal = false"></div>
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-md relative z-10 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Record Credit Payment</h3>
                
                <form @submit.prevent="submitCreditPayment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Amount ($) *</label>
                        <input v-model="payForm.amount" required type="number" step="0.01" min="0.01" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method *</label>
                        <select v-model="payForm.method" required class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none bg-white">
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reference No.</label>
                        <input v-model="payForm.reference_no" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-teal-500 outline-none">
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="showPayModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="paying" class="flex-1 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-xl shadow-md transition-colors disabled:opacity-50">
                            {{ paying ? 'Processing...' : 'Post Payment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeModal"></div>
            
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-lg relative z-10 p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-xl font-bold text-slate-800 mb-6">{{ editingCustomer ? 'Edit Customer' : 'Add New Customer' }}</h3>
                
                <form @submit.prevent="saveCustomer" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label>
                        <input v-model="form.name" required type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                            <input v-model="form.phone" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">NIC/Passport</label>
                            <input v-model="form.NIC_Passport" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input v-model="form.email" type="email" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Customer Type *</label>
                            <select v-model="form.customer_type" required class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="retail">Retail Patient</option>
                                <option value="wholesale">Wholesale Buyer</option>
                                <option value="corporate">Corporate Account</option>
                                <option value="insurance_linked">Insurance Panel Linked</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date of Birth</label>
                            <input v-model="form.dob" type="date" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
                            <select v-model="form.gender" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Credit Limit ($)</label>
                            <input v-model="form.credit_limit" type="number" step="0.01" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Credit Terms (Days)</label>
                            <input v-model="form.credit_terms_days" type="number" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                        <textarea v-model="form.address" rows="2" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="closeModal" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="saving" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg shadow-blue-600/30 transition-all flex justify-center items-center disabled:opacity-50">
                            {{ saving ? 'Saving...' : 'Save Customer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const customers = ref([]);
const loading = ref(true);
const showModal = ref(false);
const showLedgerModal = ref(false);
const showPayModal = ref(false);
const editingCustomer = ref(null);
const selectedCustomer = ref(null);
const saving = ref(false);
const paying = ref(false);
const search = ref('');

const form = ref({
    name: '',
    phone: '',
    NIC_Passport: '',
    dob: '',
    gender: '',
    email: '',
    address: '',
    customer_type: 'retail',
    credit_limit: 0,
    credit_terms_days: 0
});

const payForm = ref({
    amount: '',
    method: 'Cash',
    reference_no: ''
});

const ledgerData = ref({
    outstanding_balance: 0,
    ledger: []
});

const fetchCustomers = async () => {
    loading.value = true;
    try {
        const res = await axios.get(`/api/v1/pharmacy/customers?search=${search.value}`);
        customers.value = res.data.data;
    } catch (e) {
        console.error("Failed to load customers", e);
    } finally {
        loading.value = false;
    }
};

const openLedger = async (customer) => {
    selectedCustomer.value = customer;
    try {
        const res = await axios.get(`/api/v1/pharmacy/customers/${customer.id}/ledger`);
        ledgerData.value = res.data;
        showLedgerModal.value = true;
    } catch (e) {
        alert('Failed to load credit ledger details');
    }
};

const submitCreditPayment = async () => {
    paying.value = true;
    try {
        await axios.post(`/api/v1/pharmacy/customers/${selectedCustomer.value.id}/payment`, payForm.value);
        alert('Payment posted successfully.');
        showPayModal.value = false;
        
        // Refresh ledger data
        const res = await axios.get(`/api/v1/pharmacy/customers/${selectedCustomer.value.id}/ledger`);
        ledgerData.value = res.data;
        fetchCustomers();
    } catch (e) {
        alert(e.response?.data?.message || 'Payment recording failed.');
    } finally {
        paying.value = false;
    }
};

onMounted(() => {
    fetchCustomers();
});

const openAddModal = () => {
    editingCustomer.value = null;
    form.value = {
        name: '', phone: '', NIC_Passport: '', dob: '', gender: '', email: '', address: '', customer_type: 'retail', credit_limit: 0, credit_terms_days: 0
    };
    showModal.value = true;
};

const openEditModal = (customer) => {
    editingCustomer.value = customer;
    form.value = { ...customer };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const saveCustomer = async () => {
    saving.value = true;
    try {
        if (editingCustomer.value) {
            await axios.put(`/api/v1/pharmacy/customers/${editingCustomer.value.id}`, form.value);
        } else {
            await axios.post('/api/v1/pharmacy/customers', form.value);
        }
        closeModal();
        fetchCustomers();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to save customer details.');
        console.error(e);
    } finally {
        saving.value = false;
    }
};
</script>
