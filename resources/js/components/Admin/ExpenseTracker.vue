<template>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50/80">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Expense Tracker
            </h2>
            <button @click="showForm = !showForm" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium hover:bg-slate-700 transition-colors">
                {{ showForm ? 'Close Form' : '+ Record Expense' }}
            </button>
        </div>

        <!-- Record Expense Form -->
        <div v-if="showForm" class="p-6 bg-slate-50 border-b border-slate-200">
            <h3 class="font-semibold text-slate-700 mb-4">Record New Expense</h3>
            <div class="grid grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                    <select v-model="form.category" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-500 outline-none text-sm bg-white">
                        <option value="Salary">Salary / Payroll</option>
                        <option value="Rent">Rent</option>
                        <option value="Utilities">Utilities (Water, Electricity)</option>
                        <option value="Supplies">Office Supplies</option>
                        <option value="Maintenance">Maintenance & Repairs</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Other">Other Operational</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Amount ($)</label>
                    <input type="number" step="0.01" v-model="form.amount" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Date Incurred</label>
                    <input type="date" v-model="form.date" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <input type="text" v-model="form.notes" placeholder="Invoice #, Description..." class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-500 outline-none text-sm">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button @click="submit" :disabled="loading" class="px-6 py-2 bg-rose-600 text-white rounded-lg font-medium hover:bg-rose-700 transition-colors flex items-center gap-2">
                    <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Record Expense
                </button>
            </div>
        </div>

        <!-- Expenses List -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-slate-200">
                        <th class="p-4 text-xs font-semibold text-slate-500 uppercase">Date</th>
                        <th class="p-4 text-xs font-semibold text-slate-500 uppercase">Category</th>
                        <th class="p-4 text-xs font-semibold text-slate-500 uppercase">Amount</th>
                        <th class="p-4 text-xs font-semibold text-slate-500 uppercase">Recorded By</th>
                        <th class="p-4 text-xs font-semibold text-slate-500 uppercase">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="exp in expenses" :key="exp.id" class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-sm text-slate-600">{{ exp.date }}</td>
                        <td class="p-4 text-sm font-medium text-slate-800">
                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ exp.category }}
                            </span>
                        </td>
                        <td class="p-4 text-sm font-bold text-rose-600">-${{ parseFloat(exp.amount).toFixed(2) }}</td>
                        <td class="p-4 text-sm text-slate-500">{{ exp.user?.name || 'System' }}</td>
                        <td class="p-4 text-sm text-slate-500 italic">{{ exp.notes || '-' }}</td>
                    </tr>
                    <tr v-if="expenses.length === 0">
                        <td colspan="5" class="p-8 text-center text-slate-400">No expenses recorded yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';

const expenses = ref([]);
const showForm = ref(false);
const loading = ref(false);

const form = reactive({
    category: 'Salary',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    notes: ''
});

const fetchData = async () => {
    try {
        const res = await axios.get('/api/v1/admin/expenses');
        expenses.value = res.data.data;
    } catch (e) {
        console.error(e);
    }
};

onMounted(fetchData);

const submit = async () => {
    loading.value = true;
    try {
        await axios.post('/api/v1/admin/expenses', form);
        alert('Expense recorded successfully!');
        
        // Reset form
        form.amount = ''; form.notes = '';
        showForm.value = false;
        
        fetchData();
    } catch(e) {
        alert(e.response?.data?.message || 'Error recording expense');
    } finally {
        loading.value = false;
    }
};
</script>
