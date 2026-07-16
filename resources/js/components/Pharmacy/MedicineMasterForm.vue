<template>
    <SlideOver 
        :show="show" 
        @update:show="$emit('update:show', $event)" 
        :title="isEditing ? 'Edit Product' : 'Add New Product'"
        :saveText="isEditing ? 'Save Changes' : 'Create Product'"
        :saving="saving"
        @save="submitForm"
    >
        <form @submit.prevent="submitForm" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">SKU / Barcode</label>
                <div class="flex">
                    <input type="text" v-model="form.barcode" class="block w-full border-slate-200 rounded-l-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Scan or enter barcode">
                    <button type="button" @click="generateBarcode" class="px-3 bg-slate-100 border border-l-0 border-slate-200 rounded-r-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition-colors">
                        Generate
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Brand Name <span class="text-red-500">*</span></label>
                <input type="text" v-model="form.name" required class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Panadol Advance">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Generic Name</label>
                <input type="text" v-model="form.generic_name" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Paracetamol">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Typeable/Searchable Category -->
                <div class="relative">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                    <input 
                        type="text" 
                        v-model="form.category_name" 
                        @focus="showCategoryDropdown = true"
                        @input="filterCategories"
                        class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" 
                        placeholder="Type or select category"
                    />
                    <!-- Categories Autocomplete Dropdown -->
                    <div v-if="showCategoryDropdown && filteredCategories.length > 0" class="absolute left-0 right-0 mt-1 bg-white border border-slate-250 rounded-xl shadow-lg z-50 overflow-hidden max-h-40 overflow-y-auto divide-y divide-slate-100">
                        <button 
                            v-for="cat in filteredCategories" 
                            :key="cat.id"
                            type="button"
                            @click="selectCategory(cat)"
                            class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-slate-50 text-slate-750"
                        >
                            {{ cat.name }}
                        </button>
                    </div>
                </div>

                <!-- Typeable Subcategory -->
                <div class="relative">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Subcategory</label>
                    <input 
                        type="text" 
                        v-model="form.subcategory" 
                        @focus="showSubcategoryDropdown = true"
                        @input="filterSubcategories"
                        class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" 
                        placeholder="Type or select subcategory"
                    />
                    <!-- Subcategories Autocomplete Dropdown -->
                    <div v-if="showSubcategoryDropdown && filteredSubcategories.length > 0" class="absolute left-0 right-0 mt-1 bg-white border border-slate-250 rounded-xl shadow-lg z-50 overflow-hidden max-h-40 overflow-y-auto divide-y divide-slate-100">
                        <button 
                            v-for="sub in filteredSubcategories" 
                            :key="sub"
                            type="button"
                            @click="selectSubcategory(sub)"
                            class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-slate-50 text-slate-755"
                        >
                            {{ sub }}
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select v-model="form.status" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Strength</label>
                    <input type="text" v-model="form.strength" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. 500mg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                    <input type="text" v-model="form.unit" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Tablet, Box">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Min Stock Level</label>
                    <input type="number" v-model="form.min_stock" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Max Stock Level</label>
                    <input type="number" v-model="form.max_stock" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Reorder Level</label>
                <input type="number" v-model="form.reorder_level" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div v-if="errorMsg" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl border border-red-100">
                {{ errorMsg }}
            </div>
        </form>
    </SlideOver>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import SlideOver from '../UI/SlideOver.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    editingItem: { type: Object, default: null },
    categories: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:show', 'saved']);

const saving = ref(false);
const errorMsg = ref('');

const showCategoryDropdown = ref(false);
const showSubcategoryDropdown = ref(false);
const filteredCategories = ref([]);
const filteredSubcategories = ref([]);
const existingSubcategories = ref([]);

const form = reactive({
    barcode: '',
    name: '',
    generic_name: '',
    category_id: '',
    category_name: '',
    subcategory: '',
    strength: '',
    unit: '',
    min_stock: 0,
    max_stock: 0,
    reorder_level: 0,
    status: 'active'
});

const isEditing = computed(() => !!props.editingItem);

const generateBarcode = () => {
    form.barcode = Math.floor(100000000000 + Math.random() * 900000000000).toString();
};

const loadSubcategories = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/medicines/subcategories');
        existingSubcategories.value = res.data.data;
        filteredSubcategories.value = res.data.data;
    } catch(e) {
        console.error("Failed to load subcategories list", e);
    }
};

const filterCategories = () => {
    const query = form.category_name ? form.category_name.toLowerCase() : '';
    filteredCategories.value = props.categories.filter(cat => cat.name.toLowerCase().includes(query));
    // Reset ID if it does not match selected name (meaning they typed a new one)
    const exact = props.categories.find(cat => cat.name.toLowerCase() === query);
    form.category_id = exact ? exact.id : null;
};

const filterSubcategories = () => {
    const query = form.subcategory ? form.subcategory.toLowerCase() : '';
    filteredSubcategories.value = existingSubcategories.value.filter(sub => sub.toLowerCase().includes(query));
};

const selectCategory = (cat) => {
    form.category_id = cat.id;
    form.category_name = cat.name;
    showCategoryDropdown.value = false;
};

const selectSubcategory = (sub) => {
    form.subcategory = sub;
    showSubcategoryDropdown.value = false;
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        errorMsg.value = '';
        loadSubcategories();
        if (props.editingItem) {
            Object.assign(form, props.editingItem);
            // Fix category_id if it's null
            if (!form.category_id) {
                form.category_id = '';
                form.category_name = '';
            } else {
                const cat = props.categories.find(c => c.id === form.category_id);
                form.category_name = cat ? cat.name : '';
            }
            if (!form.subcategory) form.subcategory = '';
        } else {
            Object.assign(form, {
                barcode: '',
                name: '',
                generic_name: '',
                category_id: '',
                category_name: '',
                subcategory: '',
                strength: '',
                unit: '',
                min_stock: 0,
                max_stock: 0,
                reorder_level: 0,
                status: 'active'
            });
        }
        filteredCategories.value = props.categories;
    }
});

const submitForm = async () => {
    if (!form.name) return;
    
    saving.value = true;
    errorMsg.value = '';
    
    try {
        const payload = { ...form };
        if (payload.category_id === '') payload.category_id = null;

        if (isEditing.value) {
            await axios.put(`/api/v1/pharmacy/medicines/${props.editingItem.id}`, payload);
        } else {
            await axios.post(`/api/v1/pharmacy/medicines`, payload);
        }
        emit('saved');
        emit('update:show', false);
    } catch (e) {
        errorMsg.value = e.response?.data?.message || 'Failed to save product.';
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) {
            showCategoryDropdown.value = false;
            showSubcategoryDropdown.value = false;
        }
    });
});
</script>
