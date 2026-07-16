<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Patients</h2>
            <p class="text-sm text-slate-500 mt-1">Manage patient records and demographics.</p>
        </div>

        <!-- Data Table -->
        <DataTable 
            title="Patient Directory"
            :columns="columns"
            :data="patients"
            :loading="loading"
            actionText="Register Patient"
            @action="openForm(null)"
            @edit="openForm($event)"
            @delete="confirmDelete"
        >
            <template #name="{ item }">
                <span class="font-bold text-slate-800">{{ item.first_name }} {{ item.last_name }}</span>
            </template>
        </DataTable>

        <!-- SlideOver Form -->
        <PatientForm 
            v-model:show="showForm" 
            :editing-item="selectedItem" 
            @saved="fetchPatients"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import DataTable from '../UI/DataTable.vue';
import PatientForm from './PatientForm.vue';

const patients = ref([]);
const loading = ref(false);
const showForm = ref(false);
const selectedItem = ref(null);

const columns = [
    { key: 'mrn', label: 'MRN', isPrimary: true },
    { key: 'name', label: 'Patient Name' },
    { key: 'phone', label: 'Phone' },
    { key: 'gender', label: 'Gender' },
    { key: 'blood_group', label: 'Blood Group' },
];

const fetchPatients = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/doctor/patients');
        patients.value = response.data.data;
    } catch (e) {
        console.error("Failed to fetch patients", e);
    } finally {
        loading.value = false;
    }
};

const openForm = (item) => {
    selectedItem.value = item ? { ...item } : null;
    showForm.value = true;
};

const confirmDelete = async (item) => {
    if (confirm(`Are you sure you want to delete ${item.first_name} ${item.last_name}?`)) {
        try {
            await axios.delete(`/api/v1/doctor/patients/${item.id}`);
            fetchPatients();
        } catch (e) {
            alert('Failed to delete patient.');
        }
    }
};

onMounted(() => {
    fetchPatients();
});
</script>
