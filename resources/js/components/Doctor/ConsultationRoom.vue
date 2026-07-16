<template>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-full overflow-hidden">
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    {{ consultation.patient?.first_name }} {{ consultation.patient?.last_name }}
                </h3>
                <p class="text-sm text-slate-500 mt-1">Consultation In Progress</p>
            </div>
            <button @click="$emit('cancel')" class="text-sm text-slate-500 hover:text-red-500 font-medium px-3 py-1.5 rounded-lg transition-colors">
                Cancel
            </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-white">
            <!-- Clinical Notes -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Symptoms / Chief Complaint</label>
                    <textarea v-model="form.symptoms" rows="2" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-shadow text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Diagnosis</label>
                    <input type="text" v-model="form.diagnosis" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-shadow text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes / Instructions</label>
                    <textarea v-model="form.notes" rows="2" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-shadow text-sm"></textarea>
                </div>
            </div>

            <!-- Prescriptions section -->
            <div class="border-t border-slate-100 pt-6">
                <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Prescription
                </h4>
                
                <!-- Search Medicine -->
                <div class="relative mb-4">
                    <input 
                        type="text" 
                        v-model="searchQuery" 
                        @input="debouncedSearch"
                        placeholder="Search medicines to prescribe..." 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none text-sm bg-slate-50"
                    >
                    <div v-if="searchResults.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                        <div 
                            v-for="med in searchResults" 
                            :key="med.id" 
                            @click="addPrescription(med)"
                            class="px-4 py-2 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0 text-sm"
                        >
                            <span class="font-medium">{{ med.name }}</span> <span class="text-slate-500 text-xs">({{ med.generic_name }})</span>
                        </div>
                    </div>
                </div>

                <!-- Prescription List -->
                <div class="space-y-3">
                    <div v-for="(item, index) in form.prescriptions" :key="index" class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex gap-3 items-center relative group">
                        <div class="flex-1">
                            <p class="font-medium text-sm text-slate-800">{{ item.name }}</p>
                        </div>
                        <input v-model="item.dosage" placeholder="Dosage (e.g. 500mg)" class="w-24 px-2 py-1.5 text-xs rounded border border-slate-200">
                        <input v-model="item.frequency" placeholder="Freq (e.g. 1-0-1)" class="w-24 px-2 py-1.5 text-xs rounded border border-slate-200">
                        <input v-model="item.duration" placeholder="Duration (e.g. 5 Days)" class="w-24 px-2 py-1.5 text-xs rounded border border-slate-200">
                        
                        <button @click="removePrescription(index)" class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded-md opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <p v-if="form.prescriptions.length === 0" class="text-sm text-slate-400 italic">No medicines prescribed.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-5 border-t border-slate-100 bg-slate-50/50 flex justify-end">
            <button 
                @click="submitConsultation"
                :disabled="loading"
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-sm transition-colors disabled:opacity-50 flex items-center gap-2"
            >
                <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Complete Consultation
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const props = defineProps({
    consultation: { type: Object, required: true }
});

const emit = defineEmits(['cancel', 'completed']);

const loading = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
let searchTimeout = null;

const form = reactive({
    symptoms: props.consultation.symptoms || '',
    diagnosis: props.consultation.diagnosis || '',
    notes: props.consultation.notes || '',
    prescriptions: []
});

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    if(searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }
    searchTimeout = setTimeout(async () => {
        try {
            const res = await axios.get(`/api/v1/doctor/medicines/search?q=${searchQuery.value}`);
            searchResults.value = res.data.data;
        } catch(e) { console.error(e); }
    }, 300);
};

const addPrescription = (med) => {
    form.prescriptions.push({
        medicine_id: med.id,
        name: med.name,
        dosage: '',
        frequency: '',
        duration: ''
    });
    searchQuery.value = '';
    searchResults.value = [];
};

const removePrescription = (index) => {
    form.prescriptions.splice(index, 1);
};

const submitConsultation = async () => {
    loading.value = true;
    try {
        await axios.post(`/api/v1/doctor/consultations/${props.consultation.uuid}/prescribe`, form);
        emit('completed');
    } catch(e) {
        alert("Error saving consultation");
        console.error(e);
    } finally {
        loading.value = false;
    }
};
</script>
