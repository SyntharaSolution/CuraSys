<template>
    <div class="h-full flex flex-col p-6 w-full overflow-hidden bg-slate-50">
        
        <!-- Module Sub-navigation (Hidden for Cashiers and Sales Persons to avoid duplicate menus) -->
        <div v-if="!isOnlyCashier && !isSalesPerson" class="flex items-center gap-4 mb-4 border-b border-slate-200/60 pb-4 shrink-0">
            <router-link :to="{ name: 'POS Terminal' }" class="px-5 py-2 text-sm font-semibold rounded-xl transition-all" :class="$route.name === 'POS Terminal' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'">
                Terminal
            </router-link>
            <router-link :to="{ name: 'Cashier' }" class="px-5 py-2 text-sm font-semibold rounded-xl transition-all" :class="$route.name === 'Cashier' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'">
                Cashier Queue
            </router-link>
            <router-link :to="{ name: 'Sales Invoices' }" class="px-5 py-2 text-sm font-semibold rounded-xl transition-all" :class="$route.name === 'Sales Invoices' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'">
                Invoices / Returns
            </router-link>
            <router-link :to="{ name: 'Customers' }" class="px-5 py-2 text-sm font-semibold rounded-xl transition-all" :class="$route.name === 'Customers' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'">
                Customers
            </router-link>
        </div>

        <div class="w-full flex-1 overflow-hidden relative">
            <router-view></router-view>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const isOnlyCashier = ref(false);
const isSalesPerson = ref(false);

onMounted(() => {
    const user = JSON.parse(localStorage.getItem('auth_user') || '{}');
    const roles = user.roles || [];
    const isCashier = roles.some(r => r.name === 'Cashier');
    isSalesPerson.value = roles.some(r => r.name === 'Sales Person');

    if (isCashier) {
        // Cashiers only need access to Cashier queue initially
        isOnlyCashier.value = true;
        if (route.name === 'POS Terminal' || route.path === '/app/pos') {
            router.push({ name: 'Cashier' });
        }
    }

    // Sales Person is routed straight to Terminal (handled by AppLayout nav)
});
</script>
