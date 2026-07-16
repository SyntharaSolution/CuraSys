<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Medicine Master</h2>
            <p class="text-sm text-slate-500 mt-1">Manage the complete catalog of medicines and products.</p>
        </div>

        <!-- Data Table -->
        <DataTable 
            title="Product Catalog"
            :columns="columns"
            :data="medicines"
            :loading="loading"
            actionText="Add Product"
            @action="openForm(null)"
            @edit="openForm($event)"
            @delete="confirmDelete"
        >
            <template #name="{ item }">
                <div>
                    <div class="font-bold text-slate-800">{{ item.name }}</div>
                    <div class="text-xs text-slate-500">{{ item.generic_name || 'No Generic' }}</div>
                </div>
            </template>
            <template #category="{ item }">
                <span v-if="item.category" class="px-2 py-1 bg-slate-100 text-slate-700 text-xs rounded-lg">
                    {{ item.category.name }}
                </span>
                <span v-else class="text-slate-400 text-xs">-</span>
            </template>
            <template #prices="{ item }">
                <div class="text-xs">
                    <div><span class="text-slate-400">Cost:</span> ${{ item.purchase_price || '0.00' }}</div>
                    <div class="font-semibold text-emerald-600"><span class="text-slate-400">Sell:</span> ${{ item.selling_price || '0.00' }}</div>
                </div>
            </template>
            <template #status="{ item }">
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="item.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                    {{ item.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
            </template>
        </DataTable>

        <!-- SlideOver Form -->
        <MedicineMasterForm 
            v-model:show="showForm" 
            :editing-item="selectedItem" 
            :categories="categories"
            @saved="fetchMedicines"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import DataTable from '../UI/DataTable.vue';
import MedicineMasterForm from './MedicineMasterForm.vue';

const medicines = ref([]);
const categories = ref([]);
const loading = ref(false);
const showForm = ref(false);
const selectedItem = ref(null);

const columns = [
    { key: 'barcode', label: 'SKU/Barcode' },
    { key: 'name', label: 'Medicine details' },
    { key: 'category', label: 'Category' },
    { key: 'prices', label: 'Pricing' },
    { key: 'status', label: 'Status' },
];

const fetchMedicines = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/pharmacy/medicines');
        medicines.value = response.data.data;
    } catch (e) {
        console.error("Failed to fetch medicines", e);
    } finally {
        loading.value = false;
    }
};

const fetchCategories = async () => {
    try {
        const response = await axios.get('/api/v1/pharmacy/categories');
        categories.value = response.data.data;
    } catch (e) {
        console.error("Failed to fetch categories", e);
    }
};

const openForm = (item) => {
    selectedItem.value = item ? { ...item } : null;
    showForm.value = true;
};

const confirmDelete = async (item) => {
    if (confirm(`Are you sure you want to delete ${item.name}?`)) {
        try {
            await axios.delete(`/api/v1/pharmacy/medicines/${item.id}`);
            fetchMedicines();
        } catch (e) {
            alert('Failed to delete medicine.');
        }
    }
};

onMounted(() => {
    fetchMedicines();
    fetchCategories();
});
</script>
