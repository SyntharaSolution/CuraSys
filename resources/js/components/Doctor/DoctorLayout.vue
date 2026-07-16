<template>
    <div class="h-full flex flex-col p-6 w-full max-w-[1400px] mx-auto overflow-hidden">
        
        <!-- Module Sub-navigation -->
        <div class="flex items-center gap-4 mb-6 border-b border-slate-200/60 pb-4 shrink-0">
            <router-link :to="{ name: 'Medical Center' }" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors" :class="$route.name === 'Medical Center' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                Consultation Room
            </router-link>
            <router-link :to="{ name: 'Patients' }" class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors" :class="$route.name === 'Patients' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                Manage Patients
            </router-link>
        </div>

        <div class="flex-1 flex gap-6 overflow-hidden" v-if="$route.name === 'Medical Center'">
            <!-- Left Panel (Queue) -->
            <div class="w-1/3 flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <patient-queue 
                    :queue="queue" 
                    :loading="loadingQueue"
                    @start-consultation="handleStartConsultation"
                />
            </div>

            <!-- Right Panel (Consultation) -->
            <div class="w-2/3 flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <consultation-room 
                    :consultation="currentConsultation" 
                    @finish-consultation="handleFinishConsultation"
                />
            </div>
        </div>

        <div class="w-full flex-1 overflow-y-auto" v-else>
            <router-view></router-view>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PatientQueue from './PatientQueue.vue';
import ConsultationRoom from './ConsultationRoom.vue';

const queue = ref([]);
const loadingQueue = ref(false);
const currentConsultation = ref(null);

const fetchQueue = async () => {
    loadingQueue.value = true;
    try {
        const res = await axios.get('/api/v1/doctor/queue');
        queue.value = res.data.data;
    } catch (e) {
        console.error("Queue fetch error", e);
    } finally {
        loadingQueue.value = false;
    }
};

onMounted(() => {
    fetchQueue();
});

const handleStartConsultation = async (consultationId) => {
    try {
        const res = await axios.post(`/api/v1/doctor/consultations/${consultationId}/start`);
        currentConsultation.value = res.data.data;
        fetchQueue();
    } catch (e) {
        console.error("Start consultation error", e);
    }
};

const handleFinishConsultation = () => {
    currentConsultation.value = null;
    fetchQueue();
};
</script>
