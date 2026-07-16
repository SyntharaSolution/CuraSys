<template>
    <div class="h-full flex flex-col p-6 w-full max-w-[1400px] mx-auto space-y-6">
        <!-- Welcome Section -->
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">
                {{ isCashier ? 'Cashier Dashboard' : 'Overview' }}
            </h2>
            <p class="text-slate-500 mt-1">
                {{ isCashier ? 'Track cash register session drawer movements, counts, and settlements.' : 'Here\'s what\'s happening at your pharmacy today.' }}
            </p>
        </div>

        <!-- Cashier Metrics Row -->
        <div v-if="isCashier" class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <metric-card 
                title="Today's Cash In" 
                :value="`$${parseFloat(metrics.cash_in || 0).toFixed(2)}`" 
                color-class="bg-emerald-50 text-emerald-600"
            >
                <template #icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </template>
            </metric-card>
            <metric-card 
                title="Today's Cash Out" 
                :value="`$${parseFloat(metrics.cash_out || 0).toFixed(2)}`" 
                color-class="bg-amber-50 text-amber-600"
            >
                <template #icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </template>
            </metric-card>
            <metric-card 
                title="Today's Paid Bills" 
                :value="metrics.todays_bills || 0" 
                color-class="bg-blue-50 text-blue-600"
            >
                <template #icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </template>
            </metric-card>
            <metric-card 
                title="Returns & Refunds Sum" 
                :value="`$${parseFloat(metrics.returns_refund || 0).toFixed(2)}`" 
                color-class="bg-rose-50 text-rose-600"
            >
                <template #icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-6a4 4 0 00-8 0v6m3 0V9m3 0v6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </template>
            </metric-card>
        </div>

        <!-- Admin Metrics Row -->
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <metric-card 
                title="Today's Revenue" 
                :value="`$${parseFloat(metrics.todays_revenue || 0).toFixed(2)}`" 
                color-class="bg-emerald-50 text-emerald-600"
            >
                <template #icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </template>
            </metric-card>
            <metric-card 
                title="Monthly Revenue" 
                :value="`$${parseFloat(metrics.monthly_revenue || 0).toFixed(2)}`" 
                color-class="bg-indigo-50 text-indigo-600"
            >
                <template #icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </template>
            </metric-card>
            <metric-card 
                title="Low Stock Alerts" 
                :value="metrics.low_stock_count || 0" 
                color-class="bg-rose-50 text-rose-600"
            >
                <template #icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </template>
            </metric-card>
        </div>

        <!-- Data Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-8">
            <recent-sales-table :sales="recentSales" />
            <div v-if="!isCashier">
                <low-stock-table :items="lowStockMedicines" />
            </div>
            <div v-else class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
                <h4 class="font-bold text-slate-800">Quick Actions</h4>
                <div class="grid grid-cols-2 gap-4">
                    <router-link :to="{ name: 'Cashier' }" class="p-4 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold rounded-2xl transition-all text-center flex flex-col items-center justify-center gap-2 border border-indigo-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Collect Pending Bills
                    </router-link>
                    <router-link :to="{ name: 'Supplier Bills Collection' }" class="p-4 bg-teal-50 hover:bg-teal-100 text-teal-700 font-semibold rounded-2xl transition-all text-center flex flex-col items-center justify-center gap-2 border border-teal-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Supplier Payouts
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import MetricCard from './MetricCard.vue';
import LowStockTable from './LowStockTable.vue';
import RecentSalesTable from './RecentSalesTable.vue';
import axios from 'axios';

const metrics = ref({});
const lowStockMedicines = ref([]);
const recentSales = ref([]);

const isCashier = computed(() => {
    return !!metrics.value.is_cashier;
});

const fetchData = async () => {
    try {
        const token = localStorage.getItem('auth_token');
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }
        
        const [statsRes, stockRes, salesRes] = await Promise.all([
            axios.get('/api/v1/dashboard/stats'),
            axios.get('/api/v1/dashboard/low-stock'),
            axios.get('/api/v1/dashboard/recent-sales')
        ]);
        
        metrics.value = statsRes.data;
        lowStockMedicines.value = stockRes.data.data;
        recentSales.value = salesRes.data.data;
    } catch (e) {
        console.error("Dashboard fetch error", e);
    }
};

onMounted(() => {
    fetchData();
});
</script>
