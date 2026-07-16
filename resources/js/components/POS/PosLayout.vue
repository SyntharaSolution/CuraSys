<template>
    <div class="h-full flex flex-col w-full overflow-hidden gap-4 relative">
        

        <!-- Open Session Overlay if shift is closed -->
        <div v-if="!activeSession" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl p-8 shadow-2xl max-w-sm w-full text-center space-y-5 animate-scaleUp">
                <div class="w-14 h-14 bg-indigo-50 border border-indigo-100/50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto shadow-sm">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Open Register Shift</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed font-medium">Please open register shift and input starting cash drawer float to start taking sales.</p>
                </div>
                
                <!-- Preset Suggested Cash Float -->
                <div class="bg-indigo-50 border border-indigo-150 rounded-2xl p-4 text-center">
                    <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-widest block">Suggested Starting Float</span>
                    <p class="text-2xl font-black text-indigo-900 mt-1">${{ suggestedFloat.toFixed(2) }}</p>
                    <button type="button" @click="openingFloat = suggestedFloat" class="mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-700">
                        Use Suggested Float
                    </button>
                </div>

                <div class="space-y-3.5 text-left">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Starting Float ($) *</label>
                    <input 
                        type="number" 
                        v-model.number="openingFloat" 
                        placeholder="0.00" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none text-center font-extrabold text-lg"
                    >
                </div>

                <button 
                    @click="handleOpenSession" 
                    :disabled="!openingFloat"
                    class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-extrabold shadow-lg disabled:opacity-50 transition-all uppercase text-xs tracking-wider"
                >
                    Open Shift
                </button>
            </div>
        </div>

        <!-- Main Workspace Dual Column -->
        <div class="flex-grow flex gap-6 min-h-0 overflow-hidden">
            <!-- Left Side: Catalog Products list & Filter (Scrollable column) -->
            <div class="w-2/3 flex flex-col h-full bg-white border border-slate-200/60 rounded-[28px] overflow-hidden shadow-sm relative">
                
                <!-- Search header area -->
                <div class="p-6 border-b border-slate-100 bg-white shrink-0 space-y-4">
                    <div class="flex gap-4 items-center">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input 
                                type="text" 
                                v-model="searchQuery" 
                                @input="debouncedSearch"
                                placeholder="Search products, groceries, generic name, or scan barcode..." 
                                class="block w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-semibold shadow-sm"
                            >
                        </div>
                        <span v-if="isOffline" class="text-[10px] font-bold px-2.5 py-1 rounded bg-yellow-100 text-yellow-800 border border-yellow-200 uppercase tracking-wide shrink-0">Offline Catalog</span>
                    </div>

                    <!-- Category Pills -->
                    <div class="flex gap-2 overflow-x-auto custom-scrollbar pb-1">
                        <button 
                            @click="setCategory(null)"
                            :class="['px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors border', selectedCategoryId === null ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 border-slate-100 text-slate-600']"
                        >
                            All Categories
                        </button>
                        <button 
                            v-for="cat in categories" 
                            :key="cat.id"
                            @click="setCategory(cat.id)"
                            :class="['px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors border', selectedCategoryId === cat.id ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 border-slate-100 text-slate-600']"
                        >
                            {{ cat.name }}
                        </button>
                    </div>
                </div>

                <!-- Product Scroll Grid -->
                <div class="flex-grow overflow-y-auto p-6 bg-slate-50/30 custom-scrollbar">
                    <div v-if="loading" class="flex justify-center items-center h-full text-slate-500">
                        <svg class="animate-spin h-7 w-7 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="font-bold text-sm">Loading catalog items...</span>
                    </div>
                    
                    <div v-else-if="products.length === 0" class="flex flex-col justify-center items-center h-full text-slate-400 bg-white border border-slate-150 rounded-2xl p-12">
                        <svg class="w-14 h-14 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <p class="text-base font-bold text-slate-650">No medicines or catalog items found</p>
                        <p class="text-xs text-slate-500 mt-1">Try modifying search term or filters.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="product in products" 
                            :key="product.uuid"
                            class="bg-white rounded-2xl border border-slate-200/60 p-4 shadow-[0_2px_12px_rgba(0,0,0,0.01)] hover:shadow-[0_8px_20px_rgba(0,0,0,0.04)] hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between"
                        >
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <div class="min-w-0 pr-2">
                                        <h3 class="font-bold text-slate-800 text-sm truncate">{{ product.name }}</h3>
                                        <p class="text-[10px] text-slate-400 font-medium truncate mt-0.5">{{ product.generic_name || 'General Product' }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-slate-100 text-slate-600 shrink-0 font-mono uppercase">
                                        {{ product.unit || 'Unit' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 mb-3">
                                    <span v-if="product.strength" class="text-[10px] font-bold text-indigo-650 bg-indigo-50 border border-indigo-100/30 px-2 py-0.5 rounded-md">{{ product.strength }}</span>
                                    <span class="text-[10px] font-bold text-slate-500 bg-slate-50 border border-slate-100 px-2 py-0.5 rounded-md">{{ product.category?.name || 'Unassigned' }}</span>
                                </div>
                            </div>

                            <!-- Batches stock list -->
                            <div class="mt-3 pt-3 border-t border-slate-100/80 space-y-1.5">
                                <div v-for="batch in product.batches" :key="batch.uuid" class="flex justify-between items-center bg-slate-50/70 hover:bg-slate-50 p-2 rounded-xl border border-slate-100/80 transition-colors group">
                                    <div class="min-w-0">
                                        <span class="text-sm font-black text-slate-700">${{ parseFloat(batch.selling_price).toFixed(2) }}</span>
                                        <div class="text-[9px] text-slate-400 mt-0.5 truncate">
                                            Bch: <span class="font-bold text-slate-500 font-mono">{{ batch.batch_number }}</span>
                                            <span class="mx-1">|</span>
                                            Stock: <span :class="['font-bold', batch.available_stock > 10 ? 'text-emerald-600' : 'text-rose-500 animate-pulse']">{{ batch.available_stock }}</span>
                                        </div>
                                    </div>
                                    <button 
                                        @click="addToCart(product, batch)"
                                        :disabled="batch.available_stock <= 0"
                                        class="p-2 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white rounded-lg disabled:opacity-40 disabled:cursor-not-allowed transition-colors active:scale-95 shadow-sm"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Interactive POS Shopping Cart (Fixed Column) -->
            <div class="w-1/3 flex flex-col min-h-0 h-full bg-white border border-slate-200/60 rounded-[28px] overflow-hidden shadow-sm relative shrink-0">
                <cart 
                    :items="cartItems" 
                    :is-cashier="isCashier"
                    :is-sales-person="isSalesPerson"
                    @update-quantity="updateQuantity" 
                    @remove-item="removeItem"
                    @checkout="handleCheckout"
                    @hold-bill="handleHoldBill"
                    @show-held-bills="showHeldBillsModal = true"
                    @send-to-cashier="handleSendToCashier"
                />
                
                <checkout-modal 
                    v-if="showCheckoutModal"
                    :cart-total="cartTotal"
                    :items="cartItems"
                    :is-pending="isPendingCheckout"
                    @close="showCheckoutModal = false"
                    @process="processCheckout"
                />

                <held-bills-modal
                    v-if="showHeldBillsModal"
                    @close="showHeldBillsModal = false"
                    @resume="handleResumeBill"
                />
            </div>
        </div>

        <!-- Close Session Z-Report Modal -->
        <div v-if="showCloseSessionModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl p-6 shadow-2xl max-w-sm w-full text-center space-y-4 animate-scaleUp">
                <h3 class="text-xl font-bold text-slate-800">Close Shift Z-Report</h3>
                <p class="text-xs text-slate-500 font-medium">Log counted drawer balance and verify session variance details.</p>

                <div class="space-y-4 text-left">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Counted Cash Amount ($) *</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            v-model.number="countedCash" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none text-center font-extrabold text-lg"
                            placeholder="0.00"
                        />
                    </div>

                    <div v-if="closeErrorMessage" class="bg-rose-50 text-rose-700 p-3.5 rounded-xl border border-rose-100 text-xs space-y-2">
                        <div class="font-semibold">{{ closeErrorMessage }}</div>
                        <div>
                            <label class="block font-extrabold uppercase text-[10px] text-rose-800">Manager Override Code PIN</label>
                            <input type="password" v-model="managerOverridePin" class="w-full px-3 py-1.5 rounded-lg border border-rose-350 outline-none text-slate-800 tracking-widest text-center mt-1 font-bold">
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="showCloseSessionModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all text-xs uppercase tracking-wide">Cancel</button>
                    <button @click="handleCloseSession" class="flex-grow py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-extrabold shadow-md transition-all text-xs uppercase tracking-wide">Close Shift</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Cart from './Cart.vue';
import CheckoutModal from './CheckoutModal.vue';
import HeldBillsModal from './HeldBillsModal.vue';
import { cacheProducts, searchProductsOffline } from '../../services/OfflineService';
import axios from 'axios';

const router = useRouter();

const cartItems = ref([]);
const showCheckoutModal = ref(false);
const showHeldBillsModal = ref(false);
const isPendingCheckout = ref(false);

const activeSession = ref(null);
const openingFloat = ref('');
const suggestedFloat = ref(150.00);
const showCloseSessionModal = ref(false);
const countedCash = ref('');
const managerOverridePin = ref('');
const closeErrorMessage = ref('');

const searchQuery = ref('');
const selectedCategoryId = ref(null);
const categories = ref([]);
const products = ref([]);
const loading = ref(false);
let debounceTimeout = null;
const isOffline = ref(!navigator.onLine);

const user = ref(JSON.parse(localStorage.getItem('auth_user') || '{}'));
const isCashier = ref(false);
const isSalesPerson = ref(false);

const cartTotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
});

const loadSuggestedFloat = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/session/suggest-float');
        suggestedFloat.value = parseFloat(res.data.suggested_amount || 150.00);
        openingFloat.value = suggestedFloat.value;
    } catch (e) {
        console.error(e);
    }
};

const checkActiveSession = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/session/active');
        activeSession.value = res.data.session;
        if (!activeSession.value) {
            await loadSuggestedFloat();
        }
    } catch (e) {
        activeSession.value = null;
    }
};

const fetchCategories = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/categories');
        categories.value = res.data.data.filter(c => c.status === 'active');
    } catch (e) {
        console.error("Failed to fetch categories", e);
    }
};

const setCategory = (id) => {
    selectedCategoryId.value = id;
    fetchProducts();
};

const fetchProducts = async () => {
    loading.value = true;
    try {
        if (!navigator.onLine) {
            isOffline.value = true;
            products.value = await searchProductsOffline(searchQuery.value);
            if (selectedCategoryId.value) {
                products.value = products.value.filter(p => p.category_id === selectedCategoryId.value);
            }
            loading.value = false;
            return;
        }

        isOffline.value = false;
        let url = `/api/v1/pharmacy/pos/search-medicines?q=${searchQuery.value}`;
        if (selectedCategoryId.value) {
            url += `&category_id=${selectedCategoryId.value}`;
        }
        
        const res = await axios.get(url);
        products.value = res.data.data;

        if (!searchQuery.value && !selectedCategoryId.value) {
            await cacheProducts(products.value);
        }
    } catch (e) {
        console.error("Failed to fetch products from API, falling back to offline DB", e);
        isOffline.value = true;
        products.value = await searchProductsOffline(searchQuery.value);
        if (selectedCategoryId.value) {
            products.value = products.value.filter(p => p.category_id === selectedCategoryId.value);
        }
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        fetchProducts();
    }, 300);
};

// Global Barcode Listener
let barcodeBuffer = '';
let barcodeTimeout = null;

const handleKeyPress = (e) => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
        return;
    }

    if (e.key === 'Enter') {
        if (barcodeBuffer.length > 3) {
            searchQuery.value = barcodeBuffer;
            barcodeBuffer = '';
            fetchProducts();
        }
    } else {
        barcodeBuffer += e.key;
        clearTimeout(barcodeTimeout);
        barcodeTimeout = setTimeout(() => {
            barcodeBuffer = '';
        }, 100);
    }
};

onMounted(() => {
    isCashier.value = (user.value.roles || []).some(r => r.name === 'Cashier');
    isSalesPerson.value = (user.value.roles || []).some(r => r.name === 'Sales Person');
    fetchCategories();
    fetchProducts();
    
    if (isCashier.value) {
        checkActiveSession();
    } else {
        // Sales person does not require active cash shifts
        activeSession.value = { id: 0, opened_at: new Date().toISOString() };
    }

    window.addEventListener('online', () => { isOffline.value = false; });
    window.addEventListener('offline', () => { isOffline.value = true; fetchProducts(); });
    window.addEventListener('keypress', handleKeyPress);
});

const handleOpenSession = async () => {
    if (!openingFloat.value) return;
    try {
        const res = await axios.post('/api/v1/pharmacy/pos/session/open', {
            register_id: 1,
            opening_amount: parseFloat(openingFloat.value),
            reason: 'Opening register session from terminal layout'
        });
        activeSession.value = res.data.session;
        openingFloat.value = '';
    } catch(e) {
        alert(e.response?.data?.message || 'Failed to open register shift.');
    }
};

const handleCloseSession = async () => {
    try {
        closeErrorMessage.value = '';
        await axios.post('/api/v1/pharmacy/pos/session/close', {
            session_id: activeSession.value.id,
            counted_cash: parseFloat(countedCash.value),
            manager_override_pin: managerOverridePin.value
        });
        activeSession.value = null;
        showCloseSessionModal.value = false;
        countedCash.value = '';
        managerOverridePin.value = '';
    } catch (e) {
        if (e.response?.status === 403) {
            closeErrorMessage.value = e.response.data.message;
        } else {
            alert('Failed to close register session.');
        }
    }
};

const addToCart = (product, batch) => {
    const existing = cartItems.value.find(item => item.batch_id === batch.id);
    if (existing) {
        if (existing.quantity < batch.available_stock) {
            existing.quantity++;
        } else {
            alert("Insufficient physical batch stock available.");
        }
    } else {
        cartItems.value.push({
            medicine_id: product.id,
            batch_id: batch.id,
            name: product.name,
            batch_number: batch.batch_number,
            generic_name: product.generic_name,
            unit_price: parseFloat(batch.selling_price),
            quantity: 1,
            max_stock: batch.available_stock,
            controlled: product.category_id === 2
        });
    }
};

const updateQuantity = (batch_id, quantity) => {
    const item = cartItems.value.find(i => i.batch_id === batch_id);
    if (item) {
        if (quantity <= 0) {
            removeItem(batch_id);
        } else if (quantity <= item.max_stock) {
            item.quantity = quantity;
        } else {
            alert("Quantity exceeds available batch stock limit.");
        }
    }
};

const removeItem = (batch_id) => {
    cartItems.value = cartItems.value.filter(i => i.batch_id !== batch_id);
};

const handleCheckout = () => {
    if (cartItems.value.length === 0) return;
    isPendingCheckout.value = false;
    showCheckoutModal.value = true;
};

const handleSendToCashier = () => {
    if (cartItems.value.length === 0) return;
    isPendingCheckout.value = true;
    showCheckoutModal.value = true;
};

const handleHoldBill = async () => {
    if (cartItems.value.length === 0) return;
    try {
        await axios.post('/api/v1/pharmacy/pos/hold', {
            register_id: 1,
            cart_items: cartItems.value.map(i => ({
                medicine_id: i.medicine_id,
                batch_id: i.batch_id,
                quantity: i.quantity,
                unit_price: i.unit_price
            })),
            customer_id: null
        });
        cartItems.value = [];
        alert('Bill saved to held stack successfully.');
    } catch (e) {
        alert('Failed to hold bill.');
    }
};

const handleResumeBill = (items) => {
    cartItems.value = items.map(i => ({
        medicine_id: i.medicine_id,
        batch_id: i.batch_id,
        name: i.medicine?.name || 'Medicine',
        batch_number: i.batch?.batch_number || 'BCH',
        generic_name: i.medicine?.generic_name || '',
        unit_price: parseFloat(i.unit_price),
        quantity: parseInt(i.quantity),
        max_stock: parseInt(i.batch?.available_stock || 100),
        controlled: i.medicine?.category_id === 2
    }));
    showHeldBillsModal.value = false;
};

const processCheckout = async (payload) => {
    const status = isPendingCheckout.value ? 'pending' : 'completed';
    
    const saleData = {
        items: cartItems.value.map(i => ({
            medicine_id: i.medicine_id,
            batch_id: i.batch_id,
            quantity: i.quantity,
            unit_price: i.unit_price
        })),
        payments: payload.payments,
        discount_amount: payload.discount_amount,
        customer_id: payload.customer_id,
        verified_by_pharmacist: payload.verified_by_pharmacist,
        manager_pin: payload.manager_pin,
        status: status
    };

    try {
        await axios.post('/api/v1/pharmacy/pos/checkout', saleData);
        cartItems.value = [];
        showCheckoutModal.value = false;

        // Auto-logout for Sales Person after sending bill to cashier
        if (isSalesPerson.value && status === 'pending') {
            alert('Bill sent to cashier queue! You will now be logged out.');
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            delete axios.defaults.headers.common['Authorization'];
            router.push({ name: 'Login' });
            return;
        }

        alert(status === 'pending' ? 'Bill sent to cashier queue!' : 'Invoice printed successfully!');
    } catch (e) {
        alert(e.response?.data?.message || 'Checkout failed');
    }
};
</script>

<style scoped>
@keyframes scaleUp {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(12px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-scaleUp {
  animation: scaleUp 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
</style>
