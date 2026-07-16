<template>
    <div class="flex flex-col h-full bg-white shadow-[-4px_0_15px_-3px_rgba(0,0,0,0.05)] z-10 relative">
        <!-- Cart Header -->
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
            <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Current Order
            </h2>
            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full">
                {{ items.length }} Items
            </span>
        </div>

        <!-- Cart Items List -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50 min-h-0">
            <div v-if="items.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400 space-y-3">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <p class="text-sm font-medium">Cart is empty</p>
            </div>
            
            <transition-group name="list" tag="div" class="space-y-3">
                <div 
                    v-for="item in items" 
                    :key="item.batch_uuid" 
                    class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-col gap-2 relative group"
                >
                    <div class="flex justify-between items-start">
                        <div class="pr-6">
                            <h4 class="font-medium text-slate-800 text-sm leading-tight">{{ item.name }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Batch: {{ item.batch_number }}</p>
                        </div>
                        <span class="font-semibold text-slate-700">${{ (item.quantity * item.unit_price).toFixed(2) }}</span>
                    </div>

                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-slate-500">${{ item.unit_price }} each</span>
                        <div class="flex items-center bg-slate-50 rounded-lg border border-slate-200">
                            <button @click="$emit('update-quantity', item.batch_uuid, item.quantity - 1)" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-l-lg transition-colors">-</button>
                            <input 
                                type="number" 
                                :value="item.quantity"
                                @change="e => $emit('update-quantity', item.batch_uuid, parseInt(e.target.value))"
                                class="w-10 h-7 text-center text-sm bg-transparent border-x border-slate-200 focus:outline-none focus:bg-white"
                            >
                            <button @click="$emit('update-quantity', item.batch_uuid, item.quantity + 1)" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-r-lg transition-colors">+</button>
                        </div>
                    </div>

                    <button 
                        @click="$emit('remove-item', item.batch_uuid)"
                        class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500 hover:text-white shadow-sm"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </transition-group>
        </div>

        <!-- Checkout Section -->
        <div class="p-5 bg-white/80 backdrop-blur-md border-t border-slate-200/50 shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.05)] shrink-0">
            <div class="flex justify-between items-center mb-4 px-1">
                <span class="text-slate-500 font-medium">Subtotal</span>
                <span class="text-2xl font-bold text-slate-800 tracking-tight">${{ subtotal.toFixed(2) }}</span>
            </div>
            
            <!-- Hold / Resume (Hidden for Sales Person) -->
            <div v-if="!isSalesPerson" class="grid grid-cols-2 gap-3 mb-3">
                <button 
                    @click="$emit('hold-bill')"
                    :disabled="items.length === 0"
                    class="bg-amber-50 hover:bg-amber-100 text-amber-700 font-medium py-2.5 px-4 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all text-sm flex justify-center items-center gap-2 border border-amber-200/50"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Hold Local
                </button>
                <button 
                    @click="$emit('show-held-bills')"
                    class="bg-slate-50 hover:bg-slate-100 text-slate-700 font-medium py-2.5 px-4 rounded-xl transition-all text-sm flex justify-center items-center gap-2 border border-slate-200/50"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Resume Local
                </button>
            </div>

            <div class="flex flex-col gap-3">
                <!-- Send to Cashier (Primary action for Sales Person) -->
                <button 
                    @click="$emit('send-to-cashier')"
                    :disabled="items.length === 0"
                    :class="[
                        'w-full font-semibold py-3 px-4 rounded-xl transition-all active:scale-[0.98] flex justify-center items-center gap-2 border',
                        isSalesPerson 
                            ? 'bg-teal-600 hover:bg-teal-700 text-white shadow-lg shadow-teal-600/20 border-teal-600 disabled:opacity-50 disabled:cursor-not-allowed' 
                            : 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border-indigo-200/50'
                    ]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    {{ isSalesPerson ? 'Send Bill to Cashier' : 'Send to Cashier (Pending)' }}
                </button>

                <!-- Direct Checkout (Cashier only, hidden for Sales Person) -->
                <button 
                    v-if="isCashier && !isSalesPerson"
                    @click="$emit('checkout')"
                    :disabled="items.length === 0"
                    class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3.5 px-4 rounded-xl shadow-lg shadow-slate-900/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-[0.98] flex justify-between items-center"
                >
                    <span>Direct Checkout</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    isCashier: {
        type: Boolean,
        default: false
    },
    isSalesPerson: {
        type: Boolean,
        default: false
    }
});

defineEmits(['update-quantity', 'remove-item', 'checkout', 'hold-bill', 'show-held-bills', 'send-to-cashier']);

const subtotal = computed(() => {
    return props.items.reduce((total, item) => total + (item.quantity * item.unit_price), 0);
});
</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.list-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
</style>
