<template>
    <div class="h-full flex flex-col p-6 w-full max-w-[1400px] mx-auto overflow-hidden">
        
        <!-- Module Sub-navigation -->
        <div class="flex items-center gap-4 mb-6 border-b border-slate-200/60 pb-4 shrink-0">
            <router-link :to="{ name: 'Medicine Master' }" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors" :class="$route.name === 'Medicine Master' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                Medicine Master
            </router-link>
            <router-link :to="{ name: 'Medicine Categories' }" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors" :class="$route.name === 'Medicine Categories' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                Categories
            </router-link>
        </div>

        <div class="w-full flex-1 overflow-y-auto">
            <router-view></router-view>
        </div>
    </div>
</template>

<script setup>
</script>
