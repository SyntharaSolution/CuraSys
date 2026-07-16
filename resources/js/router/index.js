import { createRouter, createWebHistory } from 'vue-router';
import Login from '../components/Auth/Login.vue';
import AppLayout from '../components/AppLayout.vue';
import DashboardLayout from '../components/Dashboard/DashboardLayout.vue';
import PosLayout from '../components/POS/PosLayout.vue';
import DoctorLayout from '../components/Doctor/DoctorLayout.vue';
import PurchasingLayout from '../components/Purchasing/PurchasingLayout.vue';
import AdminLayout from '../components/Admin/AdminLayout.vue';

const routes = [
    {
        path: '/',
        name: 'Login',
        component: Login,
        meta: { guestOnly: true }
    },
    {
        path: '/app',
        component: AppLayout,
        meta: { requiresAuth: true },
        children: [
            { path: 'dashboard', name: 'Dashboard', component: DashboardLayout, meta: { blockedFor: ['Sales Person'] } },
            { 
                path: 'pos', 
                name: 'POS', 
                component: () => import('../components/POS/PosWrapperLayout.vue'),
                redirect: { name: 'POS Terminal' },
                children: [
                    { path: 'terminal', name: 'POS Terminal', component: PosLayout },
                    { path: 'cashier', name: 'Cashier', component: () => import('../components/POS/CashierPendingList.vue'), meta: { blockedFor: ['Sales Person'] } },
                    { path: 'invoices', name: 'Sales Invoices', component: () => import('../components/POS/InvoiceManagement.vue') },
                    { path: 'customers', name: 'Customers', component: () => import('../components/POS/CustomerManagement.vue') },
                    { path: 'supplier-bills', name: 'Supplier Bills Collection', component: () => import('../components/POS/SupplierBills.vue') }
                ]
            },
            { 
                path: 'doctor', 
                name: 'Medical Center', 
                component: DoctorLayout,
                meta: { blockedFor: ['Sales Person', 'Cashier'] },
                children: [
                    { path: 'patients', name: 'Patients', component: () => import('../components/Doctor/PatientList.vue') }
                ]
            },
            { 
                path: 'purchasing', 
                name: 'Purchasing', 
                component: PurchasingLayout,
                redirect: { name: 'Invoices' },
                children: [
                    { path: 'invoices', name: 'Invoices', component: () => import('../components/Purchasing/PurchaseInvoiceList.vue') },
                    { path: 'new', name: 'New GRN', component: () => import('../components/Purchasing/NewPurchaseForm.vue') },
                    { path: 'expiry', name: 'Expiry Monitor', component: () => import('../components/Purchasing/ExpiryMonitor.vue') },
                    { path: 'suppliers', name: 'Suppliers', component: () => import('../components/Purchasing/SupplierList.vue') }
                ]
            },
            { 
                path: 'pharmacy', 
                name: 'Pharmacy', 
                component: () => import('../components/Pharmacy/PharmacyLayout.vue'),
                redirect: { name: 'Medicine Master' },
                meta: { blockedFor: ['Sales Person', 'Cashier'] },
                children: [
                    { path: 'medicines', name: 'Medicine Master', component: () => import('../components/Pharmacy/MedicineMasterList.vue') },
                    { path: 'categories', name: 'Medicine Categories', component: () => import('../components/Pharmacy/CategoryList.vue') }
                ]
            },
            { path: 'admin', name: 'Administration', component: AdminLayout, meta: { blockedFor: ['Sales Person', 'Cashier'] } },
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

/**
 * Helper: get the primary role name from stored user data.
 */
function getUserPrimaryRole() {
    try {
        const user = JSON.parse(localStorage.getItem('auth_user') || '{}');
        const roles = user.roles || [];
        if (roles.some(r => r.name === 'Sales Person')) return 'Sales Person';
        if (roles.some(r => r.name === 'Cashier')) return 'Cashier';
        return 'Admin';
    } catch {
        return 'Admin';
    }
}

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');

    // Auth-required routes
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!token) {
            next({ name: 'Login' });
            return;
        }

        // Role-based blocking
        const role = getUserPrimaryRole();
        const blockedFor = to.meta.blockedFor || (to.matched.find(r => r.meta.blockedFor)?.meta.blockedFor);
        if (blockedFor && blockedFor.includes(role)) {
            // Redirect to their default page
            if (role === 'Sales Person') {
                next({ name: 'POS Terminal' });
            } else if (role === 'Cashier') {
                next({ name: 'Cashier' });
            } else {
                next({ name: 'Dashboard' });
            }
            return;
        }

        next();
    } else if (to.matched.some(record => record.meta.guestOnly)) {
        if (token) {
            // Redirect based on role
            const role = getUserPrimaryRole();
            if (role === 'Sales Person') {
                next({ name: 'POS Terminal' });
            } else if (role === 'Cashier') {
                next({ name: 'Cashier' });
            } else {
                next({ name: 'Dashboard' });
            }
        } else {
            next();
        }
    } else {
        next();
    }
});

export default router;
