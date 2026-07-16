<template>
    <div class="h-full flex flex-col p-6 w-full max-w-[1400px] mx-auto overflow-hidden">
        
        <!-- Module Sub-navigation -->
        <div class="flex items-center gap-4 mb-6 border-b border-slate-200/60 pb-4">
            <router-link :to="{ name: 'Invoices' }" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors" :class="$route.name === 'Invoices' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                Invoices
            </router-link>
            <router-link :to="{ name: 'New GRN' }" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors" :class="$route.name === 'New GRN' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                New Purchase (GRN)
            </router-link>
            <router-link :to="{ name: 'Expiry Monitor' }" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors" :class="$route.name === 'Expiry Monitor' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                Expiry Monitor
            </router-link>
            <router-link :to="{ name: 'Suppliers' }" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors" :class="$route.name === 'Suppliers' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                Manage Suppliers
            </router-link>
        </div>

        <div class="w-full flex-1 overflow-y-auto">
            <router-view></router-view>
        </div>
    </div>
</template>

<script setup>
</script>
