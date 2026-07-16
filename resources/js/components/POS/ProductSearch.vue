<template>
    <div class="flex flex-col h-full bg-slate-50/50">
        <!-- Search Bar and Categories -->
        <div class="p-6 border-b border-slate-100 bg-white shadow-sm z-0 shrink-0">
            <div class="relative max-w-2xl mx-auto mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span v-if="isOffline" class="text-xs font-semibold px-2.5 py-0.5 rounded bg-yellow-100 text-yellow-800">Offline Mode (Using local catalog)</span>
                </div>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none mt-7" :class="isOffline ? 'mt-[32px]' : ''">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    @input="debouncedSearch"
                    placeholder="Search medicines, generic name, or scan barcode..." 
                    class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 sm:text-sm shadow-sm"
                >
            </div>
            
            <!-- Category Filters -->
            <div class="max-w-4xl mx-auto flex gap-2 overflow-x-auto custom-scrollbar pb-2">
                <button 
                    @click="setCategory(null)"
                    class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
                    :class="selectedCategoryId === null ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                >
                    All Items
                </button>
                <button 
                    v-for="cat in categories" 
                    :key="cat.id"
                    @click="setCategory(cat.id)"
                    class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
                    :class="selectedCategoryId === cat.id ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                >
                    {{ cat.name }}
                </button>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="flex-1 overflow-y-auto p-6">
            <div v-if="loading" class="flex justify-center items-center h-full">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
            
            <div v-else-if="products.length === 0" class="flex flex-col justify-center items-center h-full text-slate-400">
                <svg class="w-16 h-16 mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <p class="text-lg font-medium text-slate-500">No medicines found</p>
                <p class="text-sm">Try adjusting your search query</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div 
                    v-for="product in products" 
                    :key="product.uuid"
                    class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:shadow-md transition-shadow duration-200 flex flex-col"
                >
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-semibold text-slate-800 text-lg">{{ product.name }}</h3>
                            <p class="text-xs text-slate-500">{{ product.generic_name }} • {{ product.unit }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                            {{ product.sku }}
                        </span>
                    </div>

                    <div class="mt-auto pt-4 space-y-2">
                        <div v-for="batch in product.batches" :key="batch.uuid" class="flex justify-between items-center bg-slate-50 p-2 rounded-lg border border-slate-100 group">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-slate-700">${{ batch.selling_price }}</span>
                                <span class="text-[10px] text-slate-400">Batch: {{ batch.batch_number }} | Stock: <span :class="batch.available_stock > 10 ? 'text-green-600' : 'text-red-500 font-bold'">{{ batch.available_stock }}</span></span>
                            </div>
                            <button 
                                @click="$emit('add-to-cart', product, batch)"
                                :disabled="batch.available_stock <= 0"
                                class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors active:scale-95"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { cacheProducts, searchProductsOffline } from '../../services/OfflineService';

const searchQuery = ref('');
const selectedCategoryId = ref(null);
const categories = ref([]);
const products = ref([]);
const loading = ref(false);
let debounceTimeout = null;
const isOffline = ref(!navigator.onLine);

const emit = defineEmits(['add-to-cart']);

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
            // Quick local filter by category if needed
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

        // If it's an empty query and no category, cache the entire catalog
        if (!searchQuery.value && !selectedCategoryId.value) {
            await cacheProducts(products.value);
        }
    } catch (e) {
        console.error("Failed to fetch products from API, falling back to offline DB", e);
        // Fallback to offline DB
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
    // If user is typing in an input, don't hijack unless it's very fast (barcode)
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
        // If it's the search query input, we still want to allow scanning, but usually scanners 
        // type very fast. We can leave it for now.
    }

    if (e.key === 'Enter') {
        if (barcodeBuffer.length > 3) {
            // It's likely a barcode scan
            searchQuery.value = barcodeBuffer;
            barcodeBuffer = '';
            fetchProducts();
        }
    } else {
        barcodeBuffer += e.key;
        clearTimeout(barcodeTimeout);
        barcodeTimeout = setTimeout(() => {
            barcodeBuffer = '';
        }, 100); // 100ms is fast enough to distinguish typing from scanning
    }
};

onMounted(() => {
    fetchCategories();
    fetchProducts();
    window.addEventListener('online', () => { isOffline.value = false; });
    window.addEventListener('offline', () => { isOffline.value = true; fetchProducts(); });
    window.addEventListener('keypress', handleKeyPress);
});

onUnmounted(() => {
    window.removeEventListener('keypress', handleKeyPress);
});
</script>
