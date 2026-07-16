<template>
    <div class="h-[calc(100vh-72px)] flex flex-col w-full p-6 overflow-hidden space-y-6">
        <!-- Header with Live connection pulse -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shrink-0">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Cashier Counter</h1>
                <p class="text-slate-500 mt-1">Review and process all active pending bills in a list layout.</p>
            </div>
            <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-100 px-3.5 py-1.5 rounded-full shrink-0 shadow-sm self-start sm:self-auto">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold text-emerald-800">Terminal Connected Live</span>
            </div>
        </div>

        <div v-if="loading" class="flex-1 flex items-center justify-center">
            <div class="flex items-center gap-3 text-slate-500">
                <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>Loading pending bills...</span>
            </div>
        </div>

        <!-- Main View Layout (Non-scrollable outer page wrapper) -->
        <div v-else class="flex-grow flex flex-col lg:flex-row gap-6 min-h-0 overflow-hidden">
            <!-- Left Side: Queue List of Pending Bills (Scrolls independently) -->
            <div class="flex-1 w-full overflow-y-auto pr-2 custom-scrollbar space-y-4 h-full">
                
                <div v-if="pendingBills.length === 0" class="flex flex-col items-center justify-center text-slate-400 bg-white/50 backdrop-blur-xl rounded-3xl border border-white/80 shadow-sm p-12 h-64">
                    <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-lg font-medium text-slate-600">No Pending Bills</p>
                    <p class="text-sm mt-1">You're all caught up!</p>
                    <button @click="fetchPendingBills" class="mt-6 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl hover:bg-indigo-100 transition-colors font-medium text-sm">
                        Refresh Queue
                    </button>
                </div>

                <div v-else class="space-y-4">
                    <div 
                        v-for="bill in pendingBills" 
                        :key="bill.uuid"
                        class="group bg-white hover:bg-slate-50/40 border border-slate-200/50 hover:border-indigo-500/20 rounded-[24px] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.01)] hover:shadow-[0_12px_30px_rgba(0,0,0,0.04)] hover:-translate-y-0.5 transition-all duration-300 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 animate-scaleUp"
                    >
                        <!-- Left: Visual Icon & Customer Details -->
                        <div class="flex items-center gap-5 min-w-0">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-50 to-teal-50 border border-emerald-100/50 flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[10px] font-bold text-slate-400 tracking-wider uppercase font-mono bg-slate-100 px-2 py-0.5 rounded-md">
                                        Ref: {{ bill.invoice_no || bill.uuid.substring(0, 8).toUpperCase() }}
                                    </span>
                                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100/30 px-2.5 py-0.5 rounded-full">
                                        {{ bill.items.length }} {{ bill.items.length === 1 ? 'Item' : 'Items' }}
                                    </span>
                                    <span class="text-[10px] font-medium text-slate-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ new Date(bill.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                    </span>
                                </div>
                                <h4 class="text-lg font-bold text-slate-800 leading-snug mt-1.5">
                                    {{ bill.customer?.name || 'Walk-in Customer' }}
                                </h4>
                                <p class="text-sm text-slate-500 font-medium mt-1 truncate">
                                    {{ bill.items.map(item => `${item.quantity}x ${item.medicine?.name}`).join(', ') }}
                                </p>
                            </div>
                        </div>

                        <!-- Right: Massive Amount & Action Button -->
                        <div class="w-full md:w-auto flex items-center justify-between md:justify-end gap-8 border-t md:border-t-0 border-slate-100/80 pt-4 md:pt-0 shrink-0">
                            <div class="text-left md:text-right">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Collect Amount</span>
                                <span class="text-3xl font-black text-emerald-600 tracking-tight leading-none mt-1 block">
                                    ${{ parseFloat(bill.net_total || bill.net_amount).toFixed(2) }}
                                </span>
                            </div>
                            <!-- Disabled if cashier register is not opened -->
                            <button 
                                @click="openPayment(bill)"
                                :disabled="!activeSession"
                                :class="['px-6 py-4 text-white font-black rounded-2xl transition-all duration-200 flex items-center gap-2 text-xs uppercase tracking-wider', activeSession ? 'bg-emerald-600 hover:bg-emerald-500 shadow-lg shadow-emerald-600/10 hover:shadow-emerald-600/20 active:scale-[0.97]' : 'bg-slate-300 cursor-not-allowed opacity-60']"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Collect Cash
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Real-time Cashier Session Summary Sidebar (Fixed / Non-scrollable) -->
            <div class="w-full lg:w-80 shrink-0 space-y-6">
                
                <!-- Shift Status Warning Card (IF MISSING starting float) -->
                <div v-if="!activeSession" class="bg-rose-50 border border-rose-200/60 rounded-3xl p-6 shadow-sm text-center space-y-4 animate-scaleUp">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-rose-900">Starting Float Missing</h4>
                        <p class="text-xs text-rose-750 mt-1 font-medium leading-relaxed">Please set your register starting float to enable cash collections.</p>
                    </div>
                    <button 
                        @click="triggerLateOpenModal" 
                        class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl transition-all shadow-md shadow-rose-600/10 text-xs uppercase tracking-wider"
                    >
                        Set Opening Float Now
                    </button>
                </div>

                <!-- Shift Summary Card (Active Float Info) -->
                <div v-else class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-sm">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Cash Shift Summary</h3>
                    
                    <div class="space-y-4">
                        <!-- Calculated Cash Drawer Balance -->
                        <div>
                            <span class="text-xs font-semibold text-slate-500">Calculated Cash Drawer</span>
                            <p class="text-3xl font-black text-slate-800 mt-1">${{ drawerBalance.toFixed(2) }}</p>
                        </div>

                        <div class="h-px bg-slate-100"></div>

                        <!-- Statistics Grid -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Today's Sales</span>
                                <p class="text-sm font-bold text-slate-700 mt-0.5">${{ shiftStats.total_sales.toFixed(2) }}</p>
                                <span class="text-[10px] text-slate-500 font-medium">({{ shiftStats.sales_count }} Paid)</span>
                            </div>
                            <div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Opening Float</span>
                                    <button 
                                        @click="triggerEditFloatModal"
                                        class="text-[10px] text-indigo-600 hover:text-indigo-700 font-bold"
                                    >
                                        Edit
                                    </button>
                                </div>
                                <p class="text-sm font-bold text-slate-700 mt-0.5">${{ parseFloat(activeSession?.opening_amount || 0).toFixed(2) }}</p>
                            </div>
                        </div>

                        <div class="h-px bg-slate-100"></div>

                        <!-- Drops and Payouts -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Safe Drops</span>
                                <p class="text-sm font-bold text-rose-500 mt-0.5">-${{ shiftStats.total_drops.toFixed(2) }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Payouts</span>
                                <p class="text-sm font-bold text-rose-500 mt-0.5">-${{ shiftStats.total_payouts.toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shift Status Info Card -->
                <div class="bg-indigo-900 text-white rounded-3xl p-6 shadow-md relative overflow-hidden shrink-0">
                    <!-- Subtle background visual pattern -->
                    <div class="absolute -right-6 -bottom-6 text-indigo-800 opacity-20 pointer-events-none transform rotate-12">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    
                    <span class="text-[10px] font-extrabold text-indigo-300 uppercase tracking-widest">Active Cash Session</span>
                    <p class="text-lg font-extrabold mt-1">Shift #{{ activeSession?.id || 'N/A' }}</p>
                    <p class="text-xs text-indigo-200 mt-2 font-medium">Opened: {{ activeSession ? new Date(activeSession.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'N/A' }}</p>
                    <p class="text-xs text-indigo-200 mt-0.5 font-medium">Cashier: {{ user.name || 'John Cashier' }}</p>
                </div>
            </div>
        </div>

        <!-- Detailed POS Payment Modal -->
        <div v-if="selectedBill" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closePayment"></div>
            
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl relative z-10 overflow-hidden flex flex-col max-h-[90vh] animate-scaleUp">
                <!-- Modal Header -->
                <div class="bg-slate-50 border-b border-slate-200/60 px-6 py-4 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Review & Finalize Payment</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Edit items, apply discounts, and configure payment methods.</p>
                    </div>
                    <button @click="closePayment" class="p-2 hover:bg-slate-200/60 rounded-full transition-colors text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Modal Body (Two-Column Layout) -->
                <div class="flex-grow overflow-y-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-6 min-h-0">
                    
                    <!-- Left Column: Search & Add, Edit Items, Discount -->
                    <div class="space-y-6 flex flex-col min-h-0">
                        
                        <!-- Search and Add Items -->
                        <div class="relative">
                            <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">Search & Add Items (Medicines, Beauty, Groceries)</h4>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input 
                                    type="text" 
                                    v-model="searchQuery" 
                                    @input="performSearch"
                                    placeholder="Type medicine, grocery, sunscreen, cosmetics..." 
                                    class="pl-9 pr-4 py-2.5 w-full bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-slate-800 font-semibold shadow-sm"
                                />
                                <div v-if="searching" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>

                                <!-- Search Results Dropdown -->
                                <div v-if="searchResults.length > 0" class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden max-h-60 overflow-y-auto divide-y divide-slate-100">
                                    <button 
                                        v-for="item in searchResults" 
                                        :key="item.id"
                                        type="button"
                                        @click="addSearchedItem(item)"
                                        class="w-full text-left p-3.5 hover:bg-slate-50 flex items-center justify-between text-xs font-semibold"
                                    >
                                        <div class="min-w-0 pr-4">
                                            <p class="text-slate-800 font-bold text-sm truncate">{{ item.name }}</p>
                                            <p class="text-slate-500 font-medium mt-0.5 truncate">{{ item.generic_name || 'General Product' }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-indigo-600 font-extrabold text-sm">${{ parseFloat(item.batches?.[0]?.selling_price || 0).toFixed(2) }}</p>
                                            <p class="text-emerald-600 mt-0.5">Stock: {{ item.batches?.[0]?.available_stock || 0 }}</p>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Items List -->
                        <div class="flex-grow flex flex-col min-h-0">
                            <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">Prescribed Items</h4>
                            <div class="border border-slate-150 rounded-2xl overflow-hidden divide-y divide-slate-100 bg-slate-50/20 flex-grow overflow-y-auto custom-scrollbar min-h-[160px]">
                                <div v-for="(item, idx) in editedItems" :key="item.uuid || item.medicine_id" class="p-3.5 flex items-center justify-between gap-4 bg-white hover:bg-slate-50/50">
                                    <div class="min-w-0 flex-grow">
                                        <p class="font-semibold text-slate-800 text-sm truncate">{{ item.medicine?.name }}</p>
                                        <p class="text-xs text-slate-500 font-medium">${{ item.unit_price.toFixed(2) }} per unit</p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <!-- Quantity Controls -->
                                        <div class="flex items-center border border-slate-200 bg-white rounded-xl shadow-sm">
                                            <button 
                                                type="button"
                                                @click="item.quantity > 1 ? item.quantity-- : removeItem(idx)"
                                                class="px-2.5 py-1.5 hover:bg-slate-50 text-slate-500 hover:text-slate-700 font-bold transition-all text-xs"
                                            >
                                                -
                                            </button>
                                            <input 
                                                type="number" 
                                                v-model.number="item.quantity" 
                                                min="1" 
                                                class="w-10 text-center text-xs font-bold bg-transparent border-none outline-none focus:ring-0 text-slate-800"
                                            />
                                            <button 
                                                type="button"
                                                @click="item.quantity++"
                                                class="px-2.5 py-1.5 hover:bg-slate-50 text-slate-500 hover:text-slate-700 font-bold transition-all text-xs"
                                            >
                                                +
                                            </button>
                                        </div>

                                        <!-- Trash Action -->
                                        <button @click="removeItem(idx)" class="p-2 hover:bg-rose-50 text-rose-500 hover:text-rose-600 rounded-xl transition-colors shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                <div v-if="editedItems.length === 0" class="p-8 text-center text-slate-400 bg-white">
                                    No items in this bill.
                                </div>
                            </div>
                        </div>

                        <!-- Discount box -->
                        <div class="space-y-4 shrink-0">
                            <div>
                                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">Apply Custom Discount ($)</h4>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">
                                        $
                                    </div>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        v-model.number="discountAmount" 
                                        placeholder="0.00" 
                                        class="pl-8 pr-4 py-2.5 w-full bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-slate-800 font-semibold"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Payments & Totals -->
                    <div class="flex flex-col justify-between">
                        <!-- Split Payments Grid -->
                        <div class="space-y-6">
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Payments Settlement</h4>
                                    <button @click="addPaymentRow" class="text-xs text-indigo-600 hover:text-indigo-700 font-bold flex items-center gap-1">
                                        + Split Payment
                                    </button>
                                </div>

                                <div class="space-y-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                    <div v-for="(pay, idx) in splitPayments" :key="idx" class="flex gap-2 items-center">
                                        <!-- Select Payment Method -->
                                        <select v-model="pay.method" class="px-3 py-2 bg-white border border-slate-200 rounded-xl outline-none text-xs text-slate-800 font-semibold w-1/2 focus:ring-2 focus:ring-indigo-500">
                                            <option v-for="method in availableMethods" :key="method" :value="method">{{ method }}</option>
                                        </select>

                                        <!-- Amount input -->
                                        <div class="relative w-1/2">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-semibold text-xs">$</div>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                v-model.number="pay.amount" 
                                                class="pl-6 pr-3 py-2 bg-white border border-slate-200 rounded-xl outline-none text-xs text-slate-800 font-bold w-full focus:ring-2 focus:ring-indigo-500"
                                            />
                                        </div>

                                        <!-- Remove Row Button -->
                                        <button @click="removePaymentRow(idx)" class="p-2 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-slate-50 transition-colors shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Totals Box -->
                            <div class="bg-indigo-50/55 border border-indigo-100/50 rounded-2xl p-4 space-y-2">
                                <div class="flex justify-between text-xs font-semibold text-slate-500">
                                    <span>Subtotal</span>
                                    <span>${{ computedSubtotal.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-semibold text-rose-500">
                                    <span>Discount Applied</span>
                                    <span>-${{ parseFloat(discountAmount || 0).toFixed(2) }}</span>
                                </div>
                                <div class="h-px bg-indigo-100/80 my-2"></div>
                                <div class="flex justify-between items-end">
                                    <span class="text-sm font-bold text-slate-700">Net Due</span>
                                    <span class="text-3xl font-black text-indigo-900">${{ computedNetTotal.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action/Info Blocks -->
                        <div class="space-y-4">
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100/50 flex flex-col gap-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Patient / Customer Info</span>
                                <div class="flex justify-between items-center mt-0.5">
                                    <p class="text-sm font-bold text-slate-700">{{ selectedBill.customer?.name || 'Walk-in Customer' }}</p>
                                    <span class="text-xs bg-slate-200/60 text-slate-600 px-2.5 py-0.5 rounded-full font-bold">
                                        {{ selectedBill.customer?.customer_code || 'WALK-IN' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-3">
                                <div v-if="!isPaymentBalanced" class="bg-amber-50 border border-amber-200/50 text-amber-800 text-xs px-3.5 py-2 rounded-xl text-center font-medium animate-pulse">
                                    Warning: Payments sum does not equal Net Due. Diff: ${{ Math.abs(splitPayments.reduce((s,p) => s + parseFloat(p.amount || 0), 0) - computedNetTotal).toFixed(2) }}
                                </div>

                                <div class="flex gap-4">
                                    <button @click="closePayment" class="flex-1 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors text-sm">
                                        Close
                                    </button>
                                    <button 
                                        @click="processPayment"
                                        :disabled="processing || !isPaymentBalanced || editedItems.length === 0"
                                        class="flex-grow py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold rounded-xl transition-all flex justify-center items-center gap-2 shadow-lg shadow-emerald-600/10 disabled:opacity-50 text-sm uppercase tracking-wider"
                                    >
                                        <svg v-if="processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Confirm Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Set/Edit Float Modal -->
        <div v-if="showFloatModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showFloatModal = false"></div>
            
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm relative z-10 overflow-hidden p-6 animate-scaleUp space-y-4">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">{{ isEditingFloat ? 'Edit Starting Float' : 'Register Starting Float' }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ isEditingFloat ? 'Correct your register float balance.' : 'Add your register float to start checkout operations.' }}</p>
                </div>

                <!-- Suggested Amount Prompt -->
                <div v-if="!isEditingFloat && suggestedFloat" class="bg-indigo-50 border border-indigo-100/50 rounded-2xl p-3 text-xs text-indigo-850 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-indigo-900 block">Suggested Float:</span>
                        <p class="text-lg font-black text-indigo-900">${{ suggestedFloat.toFixed(2) }}</p>
                        <span class="text-[9px] text-indigo-600 block leading-tight">(From last shift closing)</span>
                    </div>
                    <button type="button" @click="floatForm.amount = suggestedFloat" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-[10px] uppercase transition-colors">
                        Use Preset
                    </button>
                </div>

                <!-- Float inputs -->
                <div class="space-y-4 text-left">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Float Amount ($) *</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            v-model.number="floatForm.amount" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-800 font-extrabold text-lg text-center"
                            placeholder="0.00"
                        />
                    </div>

                    <!-- Late or edit reason is required -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Reason / Notes *</label>
                        <input 
                            type="text" 
                            v-model="floatForm.reason" 
                            :placeholder="isEditingFloat ? 'e.g. Corrected entry typo' : 'e.g. Skipped initial register prompt'" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-800 font-medium"
                        />
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex gap-3 pt-2">
                    <button @click="showFloatModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all text-xs uppercase tracking-wide">
                        Cancel
                    </button>
                    <button 
                        @click="submitFloatForm" 
                        :disabled="!floatForm.amount || !floatForm.reason"
                        class="flex-grow py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-extrabold shadow-md disabled:opacity-50 transition-all text-xs uppercase tracking-wide"
                    >
                        Confirm Float
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import axios from 'axios';

const pendingBills = ref([]);
const loading = ref(true);
const selectedBill = ref(null);
const processing = ref(false);

const editedItems = ref([]);
const discountAmount = ref(0);
const splitPayments = ref([]);
const availableMethods = ['Cash', 'Card', 'Mobile Pay', 'Store Credit', 'Insurance Claim'];

const searchQuery = ref('');
const searchResults = ref([]);
const searching = ref(false);

const activeSession = ref(null);
const drawerBalance = ref(0.00);
const shiftStats = ref({
    sales_count: 0,
    total_sales: 0.00,
    total_drops: 0.00,
    total_payouts: 0.00
});
const user = ref(JSON.parse(localStorage.getItem('auth_user') || '{}'));

// Floating opening controls
const showFloatModal = ref(false);
const isEditingFloat = ref(false);
const suggestedFloat = ref(150.00);
const floatForm = reactive({
    amount: '',
    reason: ''
});

const loadSuggestedFloat = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/session/suggest-float');
        suggestedFloat.value = parseFloat(res.data.suggested_amount || 150.00);
    } catch (e) {
        console.error("Failed to load suggested float", e);
    }
};

const triggerLateOpenModal = () => {
    isEditingFloat.value = false;
    floatForm.amount = suggestedFloat.value;
    floatForm.reason = ''; // Require them to specify a reason for opening late!
    showFloatModal.value = true;
};

const triggerEditFloatModal = () => {
    isEditingFloat.value = true;
    floatForm.amount = parseFloat(activeSession.value?.opening_amount || 0);
    floatForm.reason = ''; // require a reason for editing
    showFloatModal.value = true;
};

const submitFloatForm = async () => {
    if (!floatForm.amount) {
        alert("Please specify float amount.");
        return;
    }
    if (!floatForm.reason || floatForm.reason.trim().length < 5) {
        alert("Please enter a valid reason (min 5 characters) for logging.");
        return;
    }

    try {
        if (isEditingFloat.value) {
            await axios.post('/api/v1/pharmacy/pos/session/update-float', {
                session_id: activeSession.value.id,
                opening_amount: parseFloat(floatForm.amount),
                reason: floatForm.reason
            });
            alert("Starting float balance updated successfully.");
        } else {
            await axios.post('/api/v1/pharmacy/pos/session/open', {
                register_id: 1, // default register
                opening_amount: parseFloat(floatForm.amount),
                reason: floatForm.reason
            });
            alert("Register session opened successfully.");
        }
        showFloatModal.value = false;
        await loadActiveSession(); // refresh session state
    } catch (e) {
        alert(e.response?.data?.message || "Failed to log float balance.");
    }
};

const loadActiveSession = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/session/active');
        activeSession.value = res.data.session;
        drawerBalance.value = parseFloat(res.data.balance || 0);
        if (res.data.stats) {
            shiftStats.value = res.data.stats;
        }
    } catch(e) {
        console.error("Failed to load active session details", e);
    }
};

const performSearch = async () => {
    if (!searchQuery.value || searchQuery.value.trim().length < 2) {
        searchResults.value = [];
        return;
    }
    searching.value = true;
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/search-medicines?q=' + searchQuery.value);
        searchResults.value = res.data.data;
    } catch(e) {
        console.error(e);
    } finally {
        searching.value = false;
    }
};

const addSearchedItem = (medicine) => {
    const batch = medicine.batches?.[0];
    if (!batch) {
        alert("No active stock batches found for this item.");
        return;
    }

    // Check if already in list
    const existing = editedItems.value.find(item => item.medicine?.id === medicine.id || item.medicine_id === medicine.id);
    if (existing) {
        existing.quantity++;
    } else {
        editedItems.value.push({
            uuid: null, // represents a new item
            medicine_id: medicine.id,
            medicine: {
                id: medicine.id,
                name: medicine.name
            },
            batch_id: batch.id,
            unit_price: parseFloat(batch.selling_price),
            quantity: 1
        });
    }

    // Reset search
    searchQuery.value = '';
    searchResults.value = [];
};

const fetchPendingBills = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/pending');
        pendingBills.value = res.data.data;
    } catch (e) {
        console.error("Failed to load pending bills", e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchPendingBills();
    loadActiveSession();
    loadSuggestedFloat();
});

const openPayment = (bill) => {
    selectedBill.value = bill;
    
    // Clone items
    editedItems.value = bill.items.map(item => ({
        uuid: item.uuid,
        medicine_id: item.medicine_id,
        medicine: item.medicine,
        unit_price: parseFloat(item.unit_price),
        quantity: parseInt(item.quantity)
    }));

    discountAmount.value = parseFloat(bill.discount_amount || 0);

    // Initial split payments row equal to net due
    splitPayments.value = [{
        method: 'Cash',
        amount: parseFloat(bill.net_total || bill.net_amount)
    }];
};

const closePayment = () => {
    selectedBill.value = null;
    processing.value = false;
    searchQuery.value = '';
    searchResults.value = [];
};

const removeItem = (idx) => {
    editedItems.value.splice(idx, 1);
};

const addPaymentRow = () => {
    const sumPaid = splitPayments.value.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
    const diff = computedNetTotal.value - sumPaid;
    splitPayments.value.push({
        method: 'Card',
        amount: diff > 0 ? diff : 0
    });
};

const removePaymentRow = (index) => {
    if (splitPayments.value.length > 1) {
        splitPayments.value.splice(index, 1);
    }
};

const computedSubtotal = computed(() => {
    return editedItems.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
});

const computedNetTotal = computed(() => {
    const val = computedSubtotal.value - parseFloat(discountAmount.value || 0);
    return val > 0 ? val : 0;
});

const isPaymentBalanced = computed(() => {
    const sumPaid = splitPayments.value.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
    return Math.abs(sumPaid - computedNetTotal.value) < 0.01;
});

const processPayment = async () => {
    processing.value = true;
    try {
        const payload = {
            items: editedItems.value,
            discount_amount: parseFloat(discountAmount.value || 0),
            payments: splitPayments.value
        };

        const res = await axios.post(`/api/v1/pharmacy/pos/${selectedBill.value.uuid}/complete`, payload);
        
        alert(`Payment successful! Invoice ${res.data.sale.invoice_no} generated.`);
        closePayment();
        fetchPendingBills(); // Refresh queue
        await loadActiveSession(); // Update sidebar stats in real time!
    } catch (e) {
        alert(e.response?.data?.message || "Failed to process payment");
        console.error(e);
        processing.value = false;
    }
};
</script>

<style scoped>
@keyframes scaleUp {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-scaleUp {
  animation: scaleUp 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
</style>
