<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
        
        <!-- Modal -->
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg relative z-10 overflow-hidden flex flex-col max-h-full transform transition-all">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xl font-semibold text-slate-800">Held Bills</h3>
                <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 bg-white p-1 rounded-full shadow-sm hover:shadow transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4 overflow-y-auto bg-slate-50">
                <div v-if="bills.length === 0" class="flex flex-col items-center justify-center py-10 text-slate-400 space-y-3">
                    <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <p class="text-sm font-medium">No held bills found.</p>
                </div>
                
                <div v-else class="space-y-3">
                    <div 
                        v-for="bill in bills" 
                        :key="bill.id" 
                        class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex justify-between items-center"
                    >
                        <div>
                            <div class="font-semibold text-slate-800">Bill #{{ bill.id.slice(-6) }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ new Date(bill.timestamp).toLocaleString() }} • {{ bill.items.length }} Items</div>
                            <div class="text-sm font-bold text-blue-600 mt-1">${{ calculateTotal(bill.items).toFixed(2) }}</div>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                @click="resumeBill(bill)"
                                class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-200 transition-colors"
                            >
                                Resume
                            </button>
                            <button 
                                @click="deleteBill(bill.id)"
                                class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm font-semibold hover:bg-red-200 transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { getHeldBills, deleteHeldBill } from '../../services/OfflineService';

const emit = defineEmits(['close', 'resume']);

const bills = ref([]);

onMounted(async () => {
    bills.value = await getHeldBills();
});

const calculateTotal = (items) => {
    return items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
};

const resumeBill = async (bill) => {
    await deleteHeldBill(bill.id);
    emit('resume', bill.items);
};

const deleteBill = async (id) => {
    if (confirm('Are you sure you want to delete this held bill?')) {
        await deleteHeldBill(id);
        bills.value = await getHeldBills();
    }
};
</script>
