<template>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-full overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Today's Patient Queue
            </h3>
            <span class="text-xs font-bold bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full">{{ queue.length }} Waiting</span>
        </div>

        <div class="flex-1 overflow-y-auto p-4 bg-slate-50">
            <div v-if="loading" class="flex justify-center p-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <div v-else-if="queue.length === 0" class="text-center py-12 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <p>No patients in queue</p>
            </div>

            <div v-else class="space-y-3">
                <div v-for="consultation in queue" :key="consultation.uuid" class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-shadow">
                    <div>
                        <h4 class="font-bold text-slate-800">{{ consultation.patient?.first_name }} {{ consultation.patient?.last_name }}</h4>
                        <p class="text-xs text-slate-500 mt-1">Status: <span class="uppercase tracking-wider font-semibold" :class="statusColor(consultation.status)">{{ consultation.status }}</span></p>
                    </div>
                    <button 
                        v-if="consultation.status === 'scheduled' || consultation.status === 'in_progress'"
                        @click="$emit('start-consultation', consultation)" 
                        class="px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white rounded-lg text-sm font-medium transition-colors"
                    >
                        {{ consultation.status === 'scheduled' ? 'Start' : 'Resume' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    queue: { type: Array, required: true },
    loading: { type: Boolean, default: false }
});

defineEmits(['start-consultation']);

const statusColor = (status) => {
    switch(status) {
        case 'scheduled': return 'text-amber-500';
        case 'in_progress': return 'text-blue-500';
        case 'completed': return 'text-green-500';
        default: return 'text-slate-500';
    }
};
</script>
