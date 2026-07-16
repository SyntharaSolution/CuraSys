<template>
    <SlideOver 
        :show="show" 
        @update:show="$emit('update:show', $event)" 
        :title="isEditing ? 'Edit Category' : 'New Category'"
        :saveText="isEditing ? 'Save Changes' : 'Create Category'"
        :saving="saving"
        @save="submitForm"
    >
        <form @submit.prevent="submitForm" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                <input type="text" v-model="form.name" required class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Antibiotics">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea v-model="form.description" rows="3" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Optional description..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select v-model="form.status" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div v-if="errorMsg" class="text-sm text-red-600 bg-red-50 p-3 rounded-xl border border-red-100">
                {{ errorMsg }}
            </div>
        </form>
    </SlideOver>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue';
import SlideOver from '../UI/SlideOver.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    editingItem: { type: Object, default: null }
});

const emit = defineEmits(['update:show', 'saved']);

const saving = ref(false);
const errorMsg = ref('');

const form = reactive({
    name: '',
    description: '',
    status: 'active'
});

const isEditing = computed(() => !!props.editingItem);

// Populate form when modal opens
watch(() => props.show, (newVal) => {
    if (newVal) {
        errorMsg.value = '';
        if (props.editingItem) {
            Object.assign(form, props.editingItem);
        } else {
            Object.assign(form, {
                name: '',
                description: '',
                status: 'active'
            });
        }
    }
});

const submitForm = async () => {
    if (!form.name) return;
    
    saving.value = true;
    errorMsg.value = '';
    
    try {
        if (isEditing.value) {
            await axios.put(`/api/v1/pharmacy/categories/${props.editingItem.id}`, form);
        } else {
            await axios.post(`/api/v1/pharmacy/categories`, form);
        }
        emit('saved');
        emit('update:show', false);
    } catch (e) {
        errorMsg.value = e.response?.data?.message || 'Failed to save category.';
    } finally {
        saving.value = false;
    }
};
</script>
