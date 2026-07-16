<template>
    <SlideOver 
        :show="show" 
        @update:show="$emit('update:show', $event)" 
        :title="isEditing ? 'Edit Patient' : 'Register Patient'"
        :saveText="isEditing ? 'Save Changes' : 'Register Patient'"
        :saving="saving"
        @save="submitForm"
    >
        <form @submit.prevent="submitForm" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">MRN (Medical Record Number) <span class="text-red-500">*</span></label>
                <input type="text" v-model="form.mrn" required class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="MRN-10001">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" v-model="form.first_name" required class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="John">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" v-model="form.last_name" required class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Doe">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NIC/Passport</label>
                    <input type="text" v-model="form.nic" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Date of Birth</label>
                    <input type="date" v-model="form.date_of_birth" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Gender <span class="text-red-500">*</span></label>
                    <select v-model="form.gender" required class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Blood Group</label>
                    <select v-model="form.blood_group" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Unknown</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                    <input type="text" v-model="form.phone" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" v-model="form.email" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                <textarea v-model="form.address" rows="2" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Allergies</label>
                <textarea v-model="form.allergies" rows="2" class="block w-full border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="List any known allergies..."></textarea>
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
    mrn: '',
    first_name: '',
    last_name: '',
    nic: '',
    phone: '',
    email: '',
    date_of_birth: '',
    gender: 'male',
    blood_group: '',
    address: '',
    allergies: ''
});

const isEditing = computed(() => !!props.editingItem);

// Populate form when modal opens
watch(() => props.show, (newVal) => {
    if (newVal) {
        errorMsg.value = '';
        if (props.editingItem) {
            Object.assign(form, props.editingItem);
        } else {
            // Generate a random MRN for convenience in MVP
            const randomMrn = 'MRN-' + Math.floor(10000 + Math.random() * 90000);
            Object.assign(form, {
                mrn: randomMrn,
                first_name: '',
                last_name: '',
                nic: '',
                phone: '',
                email: '',
                date_of_birth: '',
                gender: 'male',
                blood_group: '',
                address: '',
                allergies: ''
            });
        }
    }
});

const submitForm = async () => {
    if (!form.first_name || !form.last_name || !form.mrn) return;
    
    saving.value = true;
    errorMsg.value = '';
    
    try {
        if (isEditing.value) {
            await axios.put(`/api/v1/doctor/patients/${props.editingItem.id}`, form);
        } else {
            await axios.post(`/api/v1/doctor/patients`, form);
        }
        emit('saved');
        emit('update:show', false);
    } catch (e) {
        errorMsg.value = e.response?.data?.message || 'Failed to save patient.';
    } finally {
        saving.value = false;
    }
};
</script>
