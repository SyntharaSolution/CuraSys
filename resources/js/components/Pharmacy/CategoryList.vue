<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Medicine Categories</h2>
            <p class="text-sm text-slate-500 mt-1">Manage therapeutic classes and product categories.</p>
        </div>

        <!-- Data Table -->
        <DataTable 
            title="Category Directory"
            :columns="columns"
            :data="categories"
            :loading="loading"
            actionText="New Category"
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
        <CategoryForm 
            v-model:show="showForm" 
            :editing-item="selectedItem" 
            @saved="fetchCategories"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import DataTable from '../UI/DataTable.vue';
import CategoryForm from './CategoryForm.vue';

const categories = ref([]);
const loading = ref(false);
const showForm = ref(false);
const selectedItem = ref(null);

const columns = [
    { key: 'name', label: 'Category Name', isPrimary: true },
    { key: 'description', label: 'Description' },
    { key: 'status', label: 'Status' },
];

const fetchCategories = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/pharmacy/categories');
        categories.value = response.data.data;
    } catch (e) {
        console.error("Failed to fetch categories", e);
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
            await axios.delete(`/api/v1/pharmacy/categories/${item.id}`);
            fetchCategories();
        } catch (e) {
            alert('Failed to delete category.');
        }
    }
};

onMounted(() => {
    fetchCategories();
});
</script>
