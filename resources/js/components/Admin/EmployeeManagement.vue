<template>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50/80">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Employee Management
            </h2>
            <button @click="showForm = !showForm" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium hover:bg-slate-700 transition-colors">
                {{ showForm ? 'Close Form' : '+ New Employee' }}
            </button>
        </div>

        <!-- Add Employee Form -->
        <div v-if="showForm" class="p-6 bg-slate-50 border-b border-slate-200">
            <h3 class="font-semibold text-slate-700 mb-4">Add New Employee Profile</h3>
            <div class="grid grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                    <input type="text" v-model="form.name" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                    <input type="email" v-model="form.email" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-500 outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">System Role</label>
                    <select v-model="form.role" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-500 outline-none text-sm bg-white">
                        <option value="" disabled>Select Role</option>
                        <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" v-model="form.password" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-slate-500 outline-none text-sm">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button @click="submit" :disabled="loading" class="px-6 py-2 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-colors flex items-center gap-2">
                    <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Save Employee
                </button>
            </div>
        </div>

        <!-- Employee List -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b border-slate-200">
                        <th class="p-4 text-xs font-semibold text-slate-500 uppercase">Employee ID</th>
                        <th class="p-4 text-xs font-semibold text-slate-500 uppercase">Name</th>
                        <th class="p-4 text-xs font-semibold text-slate-500 uppercase">Email</th>
                        <th class="p-4 text-xs font-semibold text-slate-500 uppercase">Role</th>
                        <th class="p-4 text-xs font-semibold text-slate-500 uppercase">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-sm text-slate-500 font-mono">USR-{{ user.id.toString().padStart(4, '0') }}</td>
                        <td class="p-4 text-sm font-medium text-slate-800 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                {{ user.name.charAt(0) }}
                            </div>
                            {{ user.name }}
                        </td>
                        <td class="p-4 text-sm text-slate-600">{{ user.email }}</td>
                        <td class="p-4 text-sm">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700" v-if="user.roles.length > 0">
                                {{ user.roles[0].name }}
                            </span>
                            <span v-else class="text-slate-400 italic">No Role</span>
                        </td>
                        <td class="p-4 text-sm text-slate-500">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="5" class="p-8 text-center text-slate-400">Loading employees...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';

const users = ref([]);
const roles = ref([]);
const showForm = ref(false);
const loading = ref(false);

const form = reactive({
    name: '',
    email: '',
    password: '',
    role: ''
});

const fetchData = async () => {
    try {
        const res = await axios.get('/api/v1/admin/employees');
        users.value = res.data.users;
        roles.value = res.data.roles;
    } catch (e) {
        console.error(e);
    }
};

onMounted(fetchData);

const submit = async () => {
    loading.value = true;
    try {
        await axios.post('/api/v1/admin/employees', form);
        alert('Employee added successfully!');
        
        // Reset form
        form.name = ''; form.email = ''; form.password = ''; form.role = '';
        showForm.value = false;
        
        fetchData();
    } catch(e) {
        alert(e.response?.data?.message || 'Error adding employee');
    } finally {
        loading.value = false;
    }
};
</script>
