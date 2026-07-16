import { createApp } from 'vue';
import axios from 'axios';
import router from './router';

import AppLayout from './components/AppLayout.vue';
import PosLayout from './components/POS/PosLayout.vue';
import DashboardLayout from './components/Dashboard/DashboardLayout.vue';
import DoctorLayout from './components/Doctor/DoctorLayout.vue';
import PurchasingLayout from './components/Purchasing/PurchasingLayout.vue';
import AdminLayout from './components/Admin/AdminLayout.vue';
import Login from './components/Auth/Login.vue';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = localStorage.getItem('auth_token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

const app = createApp({});

app.use(router);

// Components
app.component('app-layout', AppLayout);
app.component('pos-layout', PosLayout);
app.component('dashboard-layout', DashboardLayout);
app.component('doctor-layout', DoctorLayout);
app.component('purchasing-layout', PurchasingLayout);
app.component('admin-layout', AdminLayout);
app.component('login-page', Login);

app.mount('#app');
