<template>
    <div class="h-full flex flex-col p-6 max-w-[1400px] w-full mx-auto grid grid-cols-12 gap-8 overflow-hidden">
        
        <!-- Sidebar Navigation -->
        <div class="col-span-3 h-full">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden h-full flex flex-col">
                <nav class="flex flex-col flex-1">
                    <button 
                        @click="activeTab = 'employees'"
                        class="px-6 py-4 text-left font-medium flex items-center gap-3 transition-colors border-l-4"
                        :class="activeTab === 'employees' ? 'border-slate-800 bg-slate-50 text-slate-800' : 'border-transparent text-slate-500 hover:bg-slate-50'"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        HR & Employees
                    </button>
                    <button 
                        @click="activeTab = 'expenses'"
                        class="px-6 py-4 text-left font-medium flex items-center gap-3 transition-colors border-l-4"
                        :class="activeTab === 'expenses' ? 'border-slate-800 bg-slate-50 text-slate-800' : 'border-transparent text-slate-500 hover:bg-slate-50'"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Accounting & Expenses
                    </button>
                </nav>
            </div>
        </div>

        <!-- Content Area -->
        <div class="col-span-9 h-full overflow-y-auto">
            <employee-management v-if="activeTab === 'employees'" />
            <expense-tracker v-if="activeTab === 'expenses'" />
        </div>
        
    </div>
</template>

<script setup>
import { ref } from 'vue';
import EmployeeManagement from './EmployeeManagement.vue';
import ExpenseTracker from './ExpenseTracker.vue';

const activeTab = ref('employees');
</script>
