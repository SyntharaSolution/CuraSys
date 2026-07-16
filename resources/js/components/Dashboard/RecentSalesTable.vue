<template>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Recent Sales
            </h3>
        </div>
        
        <div class="overflow-y-auto flex-1 p-0">
            <ul class="divide-y divide-slate-100">
                <li v-if="sales.length === 0" class="px-6 py-8 text-center text-slate-400 text-sm">
                    No recent sales.
                </li>
                <li v-for="sale in sales" :key="sale.uuid" class="px-6 py-4 hover:bg-slate-50 transition-colors flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-slate-800">Sale #{{ sale.uuid.split('-')[0] }}</p>
                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                            {{ sale.items_count }} items • {{ sale.created_at }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-900">${{ parseFloat(sale.total_amount).toFixed(2) }}</p>
                        <p class="text-[10px] uppercase font-semibold text-slate-400 mt-0.5">{{ sale.payment_method }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
defineProps({
    sales: {
        type: Array,
        required: true,
        default: () => []
    }
});
</script>
