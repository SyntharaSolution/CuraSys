<template>
    <div class="bg-white/70 backdrop-blur-xl border border-slate-200/50 rounded-2xl shadow-sm overflow-hidden">
        <!-- Header Actions -->
        <div class="p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800 tracking-tight">{{ title }}</h3>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <!-- Search -->
                <div class="relative flex-1 sm:w-64">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input 
                        type="text" 
                        v-model="searchQuery" 
                        placeholder="Search..." 
                        class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 block pl-10 p-2.5 transition-shadow"
                    >
                </div>
                
                <!-- Primary Action Button -->
                <button v-if="actionText" @click="$emit('action')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl text-sm px-4 py-2.5 transition-colors whitespace-nowrap shadow-sm shadow-indigo-500/30 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    {{ actionText }}
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th v-for="col in columns" :key="col.key" scope="col" class="px-6 py-4 font-semibold tracking-wider">
                            {{ col.label }}
                        </th>
                        <th scope="col" class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading" class="bg-white">
                        <td :colspan="columns.length + 1" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-6 h-6 animate-spin mx-auto mb-2 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Loading data...
                        </td>
                    </tr>
                    <tr v-else-if="filteredData.length === 0" class="bg-white">
                        <td :colspan="columns.length + 1" class="px-6 py-12 text-center text-slate-400">
                            No records found.
                        </td>
                    </tr>
                    <tr v-else v-for="item in filteredData" :key="item.id" class="bg-white border-b border-slate-50 hover:bg-slate-50/50 transition-colors group">
                        <td v-for="col in columns" :key="col.key" class="px-6 py-4">
                            <slot :name="col.key" :item="item">
                                <span class="font-medium text-slate-800" v-if="col.isPrimary">{{ item[col.key] }}</span>
                                <span v-else>{{ item[col.key] || '-' }}</span>
                            </slot>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="$emit('edit', item)" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button @click="$emit('delete', item)" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    title: { type: String, default: 'Data Table' },
    columns: { type: Array, required: true },
    data: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    actionText: { type: String, default: null }
});

const emit = defineEmits(['action', 'edit', 'delete']);
const searchQuery = ref('');

const filteredData = computed(() => {
    if (!searchQuery.value) return props.data;
    
    const query = searchQuery.value.toLowerCase();
    return props.data.filter(item => {
        return props.columns.some(col => {
            const val = item[col.key];
            return val && String(val).toLowerCase().includes(query);
        });
    });
});
</script>
