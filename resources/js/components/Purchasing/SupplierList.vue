<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Suppliers</h2>
            <p class="text-sm text-slate-500 mt-1">Manage vendor records, contact details, and status.</p>
        </div>

        <!-- Data Table -->
        <DataTable 
            title="Supplier Directory"
            :columns="columns"
            :data="suppliers"
            :loading="loading"
            actionText="Add Supplier"
            @action="openForm(null)"
            @edit="openForm($event)"
            @delete="confirmDelete"
        >
            <template #status="{ item }">
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="item.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                    {{ item.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
            </template>
        </DataTable>

        <!-- SlideOver Form -->
        <SupplierForm 
            v-model:show="showForm" 
            :editing-item="selectedItem" 
            @saved="fetchSuppliers"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import DataTable from '../UI/DataTable.vue';
import SupplierForm from './SupplierForm.vue';

const suppliers = ref([]);
const loading = ref(false);
const showForm = ref(false);
const selectedItem = ref(null);

const columns = [
    { key: 'name', label: 'Company Name', isPrimary: true },
    { key: 'contact_person', label: 'Contact Person' },
    { key: 'email', label: 'Email Address' },
    { key: 'phone', label: 'Phone' },
    { key: 'status', label: 'Status' },
];

const fetchSuppliers = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/pharmacy/suppliers');
        suppliers.value = response.data.data;
    } catch (e) {
        console.error("Failed to fetch suppliers", e);
    } finally {
        loading.value = false;
    }
};

const openForm = (item) => {
    selectedItem.value = item ? { ...item } : null;
    showForm.value = true;
};

const confirmDelete = async (item) => {
    if (confirm(`Are you sure you want to delete ${item.name}?`)) {
        try {
            await axios.delete(`/api/v1/pharmacy/suppliers/${item.id}`);
            fetchSuppliers();
        } catch (e) {
            alert('Failed to delete supplier.');
        }
    }
};

onMounted(() => {
    fetchSuppliers();
});
</script>
