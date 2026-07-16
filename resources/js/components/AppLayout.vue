<template>
    <!-- Cashier Terminal Layout (Light Glassmorphic UI matching sub-pages) -->
    <div v-if="isCashier" class="h-screen bg-slate-50 flex flex-col font-sans text-slate-800 overflow-hidden relative">
        
        <!-- Cashier Top Navigation Bar -->
        <header class="bg-white border-b border-slate-200/80 px-6 py-3 flex items-center justify-between shrink-0 shadow-sm z-30">
            <div class="flex items-center gap-10">
                <!-- Brand logo -->
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-tr from-indigo-600 to-teal-500 p-2.5 rounded-xl shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <span class="text-lg font-bold tracking-tight text-slate-800 flex items-center gap-2">
                        CuraSys
                        <span class="text-[9px] bg-indigo-50 text-indigo-700 border border-indigo-100 px-2 py-0.5 rounded font-extrabold tracking-wider uppercase">Cashier Terminal</span>
                    </span>
                </div>

                <!-- Horizontal sub-nav links -->
                <nav class="flex items-center gap-1 bg-slate-100/80 p-1 rounded-xl border border-slate-200/50">
                    <router-link v-if="!isCashier" :to="{ name: 'Dashboard' }" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-800" active-class="bg-white text-indigo-700 shadow-sm border border-slate-200/30">
                        Dashboard
                    </router-link>
                    <router-link :to="{ name: 'Cashier' }" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-800" active-class="bg-white text-indigo-700 shadow-sm border border-slate-200/30">
                        Queue ({{ backendPendingCount }} Bills)
                    </router-link>
                    <router-link :to="{ name: 'Sales Invoices' }" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-800" active-class="bg-white text-indigo-700 shadow-sm border border-slate-200/30">
                        Invoices & Returns
                    </router-link>
                    <router-link :to="{ name: 'Supplier Bills Collection' }" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-800" active-class="bg-white text-indigo-700 shadow-sm border border-slate-200/30">
                        Supplier Invoices
                    </router-link>
                </nav>
            </div>

            <!-- Cash Session Float / Logged Cashier Profile details -->
            <div class="flex items-center gap-4">
                <!-- Log Payout / Drop Cash Button -->
                <button 
                    @click="openDrawerModal"
                    class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100/80 border border-indigo-100 text-indigo-700 font-semibold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-sm"
                >
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Drawer Action
                </button>

                <div class="h-6 w-px bg-slate-200"></div>

                <div class="flex items-center gap-3">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'John Cashier')}&background=4f46e5&color=fff`" class="w-9 h-9 rounded-full ring-2 ring-slate-100">
                    <div class="text-left hidden md:block">
                        <p class="text-sm font-semibold text-slate-800 leading-tight">{{ user.name || 'John Cashier' }}</p>
                        <button @click="logout" class="text-xs text-rose-500 hover:text-rose-600 font-medium transition-colors">Sign Out</button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace Area -->
        <main class="flex-1 relative overflow-hidden bg-slate-50/50">
            <router-view v-slot="{ Component }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </router-view>
        </main>
        
        <!-- Initial Open Session / Start Float Modal Prompt -->
        <div v-if="showOpenSessionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm z-[99]">
            <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-2xl max-w-sm w-full space-y-4 animate-scaleUp">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100/50 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Welcome, Start Your Shift</h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Please enter your starting cash drawer balance/float to begin checkouts.</p>
                </div>

                <!-- Previous Balance Suggestion Block -->
                <div class="bg-indigo-50 border border-indigo-100/50 rounded-2xl p-4 text-center">
                    <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-widest block">Suggested Float (From Last Close)</span>
                    <p class="text-2xl font-black text-indigo-900 mt-1">${{ suggestedFloat.toFixed(2) }}</p>
                    <button type="button" @click="openSessionForm.opening_amount = suggestedFloat" class="mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-700">
                        Use Suggested Amount
                    </button>
                </div>

                <div class="space-y-3.5 text-left text-slate-700">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Float Starting Amount ($) *</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            v-model.number="openSessionForm.opening_amount" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-800 font-extrabold text-lg text-center"
                            placeholder="0.00"
                        />
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="skipFloatPrompt" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all text-xs uppercase tracking-wide">
                        Skip / Later
                    </button>
                    <button @click="submitOpenSession" class="flex-grow py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-extrabold shadow-md transition-all text-xs uppercase tracking-wide">
                        Open Session
                    </button>
                </div>
            </div>
        </div>

        <!-- Refined/Practical Cash Drawer Action Modal -->
        <div v-if="showCashMovementModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-fadeIn">
            <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-2xl max-w-md w-full space-y-5 animate-scaleUp">
                <!-- Modal Header -->
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Drawer Control Panel</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Manage cash drawer drops and payouts.</p>
                    </div>
                    <button @click="showCashMovementModal = false" class="p-2 hover:bg-slate-100 rounded-full transition-colors text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Live Balance Preview Block -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 border border-slate-150 rounded-2xl p-3 text-center">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Current Cash</span>
                        <p class="text-xl font-black text-slate-700 mt-0.5">${{ drawerBalance.toFixed(2) }}</p>
                    </div>
                    <div :class="['border rounded-2xl p-3 text-center transition-colors', expectedNewBalance < 0 ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50 border-emerald-150 text-emerald-800']">
                        <span class="text-[9px] font-bold uppercase tracking-wider block">New Expected Cash</span>
                        <p class="text-xl font-black mt-0.5">${{ expectedNewBalance.toFixed(2) }}</p>
                    </div>
                </div>

                <!-- Practical IN/OUT Card Toggles -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 text-center">Select Direction</label>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- CASH IN -->
                        <button 
                            type="button"
                            @click="setMovementMode('in')"
                            :class="['p-4 rounded-2xl border text-center transition-all flex flex-col items-center justify-center gap-2 group', movementForm.mode === 'in' ? 'bg-emerald-50 border-emerald-300 text-emerald-800 shadow-sm ring-2 ring-emerald-500/20' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50']"
                        >
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-700 group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"></path></svg>
                            </div>
                            <span class="font-extrabold text-sm uppercase tracking-wide">Cash In</span>
                        </button>

                        <!-- CASH OUT -->
                        <button 
                            type="button"
                            @click="setMovementMode('out')"
                            :class="['p-4 rounded-2xl border text-center transition-all flex flex-col items-center justify-center gap-2 group', movementForm.mode === 'out' ? 'bg-rose-50 border-rose-300 text-rose-800 shadow-sm ring-2 ring-rose-500/20' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50']"
                        >
                            <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-700 group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 11l7-7 7 7m-14 6l7-7 7 7"></path></svg>
                            </div>
                            <span class="font-extrabold text-sm uppercase tracking-wide">Cash Out</span>
                        </button>
                    </div>
                </div>

                <!-- Sub-type Selection & Form Inputs -->
                <div class="space-y-3.5 text-left text-slate-700">
                    <!-- Autocomplete / Typeable Subcategory field -->
                    <div class="relative">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Action Sub-Category *</label>
                        <input 
                            type="text" 
                            v-model="movementForm.subcategory" 
                            @focus="showMovementSubDropdown = true"
                            @input="filterMovementSubs"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-800 font-semibold shadow-sm"
                            placeholder="Type or select sub-category"
                        />
                        <!-- Dropdown Options -->
                        <div v-if="showMovementSubDropdown && filteredMovementSubs.length > 0" class="absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-[99] overflow-hidden max-h-40 overflow-y-auto divide-y divide-slate-100">
                            <button 
                                v-for="sub in filteredMovementSubs" 
                                :key="sub"
                                type="button"
                                @click="selectMovementSub(sub)"
                                class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-slate-50 text-slate-700"
                            >
                                {{ sub }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Amount ($) *</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            v-model.number="movementForm.amount" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-800 font-extrabold text-lg text-center"
                            placeholder="0.00"
                        />
                    </div>

                    <!-- Quick Amount Presets -->
                    <div class="flex items-center gap-1.5 justify-center py-1">
                        <button 
                            v-for="amt in [10, 20, 50, 100, 200, 500]" 
                            :key="amt"
                            type="button"
                            @click="movementForm.amount = amt"
                            class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-[10px] font-bold text-slate-600 transition-colors shadow-sm"
                        >
                            +${{ amt }}
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Reason / Notes *</label>
                        <input 
                            type="text" 
                            v-model="movementForm.reason" 
                            placeholder="e.g. Mid-shift vault transfer" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-800 font-semibold"
                        />
                    </div>
                </div>

                <!-- Submit / Error Block -->
                <div class="space-y-3">
                    <div v-if="expectedNewBalance < 0" class="bg-rose-50 border border-rose-200 text-rose-800 text-xs px-3.5 py-2.5 rounded-xl text-center font-bold animate-pulse">
                        Warning: Transaction exceeds current drawer cash balance.
                    </div>

                    <div class="flex gap-3">
                        <button @click="showCashMovementModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-all text-xs uppercase tracking-wide">Cancel</button>
                        <button 
                            @click="submitCashMovement" 
                            :disabled="expectedNewBalance < 0 || !movementForm.amount || !movementForm.reason || !movementForm.subcategory"
                            class="flex-grow py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-extrabold shadow-md shadow-indigo-600/10 disabled:opacity-50 transition-all text-xs uppercase tracking-wide"
                        >
                            Post Action
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Person Terminal Layout (No sidebar, no dashboard, restricted nav) -->
    <div v-else-if="isSalesPerson" class="h-screen bg-slate-50 flex flex-col font-sans text-slate-800 overflow-hidden relative">
        
        <!-- Sales Person Top Navigation Bar -->
        <header class="bg-white border-b border-slate-200/80 px-6 py-3 flex items-center justify-between shrink-0 shadow-sm z-30">
            <div class="flex items-center gap-10">
                <!-- Brand logo -->
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-tr from-teal-600 to-emerald-500 p-2.5 rounded-xl shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <span class="text-lg font-bold tracking-tight text-slate-800 flex items-center gap-2">
                        CuraSys
                        <span class="text-[9px] bg-teal-50 text-teal-700 border border-teal-100 px-2 py-0.5 rounded font-extrabold tracking-wider uppercase">Sales Terminal</span>
                    </span>
                </div>

                <!-- Horizontal sub-nav links -->
                <nav class="flex items-center gap-1 bg-slate-100/80 p-1 rounded-xl border border-slate-200/50">
                    <router-link :to="{ name: 'POS Terminal' }" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-800" active-class="bg-white text-teal-700 shadow-sm border border-slate-200/30">
                        Terminal
                    </router-link>
                    <router-link :to="{ name: 'Purchasing' }" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-800" active-class="bg-white text-teal-700 shadow-sm border border-slate-200/30">
                        New GRN
                    </router-link>
                    <router-link :to="{ name: 'Customers' }" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-800" active-class="bg-white text-teal-700 shadow-sm border border-slate-200/30">
                        Customers
                    </router-link>
                    <router-link :to="{ name: 'Suppliers' }" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-800" active-class="bg-white text-teal-700 shadow-sm border border-slate-200/30">
                        Suppliers
                    </router-link>
                </nav>
            </div>

            <!-- Sales Person Profile -->
            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <p class="text-[10px] font-bold text-teal-600 uppercase tracking-wider">Sales Person</p>
                    <p class="text-xs font-bold text-slate-800">{{ user.name || 'Sales Person' }}</p>
                </div>
                <div class="h-6 w-px bg-slate-200"></div>
                <div class="flex items-center gap-3">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'SP')}&background=0d9488&color=fff`" class="w-9 h-9 rounded-full ring-2 ring-slate-100">
                    <button @click="logout" class="text-xs text-rose-500 hover:text-rose-600 font-bold transition-colors bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg border border-rose-100">
                        Sign Out
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Workspace Area -->
        <main class="flex-1 relative overflow-hidden bg-slate-50/50">
            <router-view v-slot="{ Component }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </router-view>
        </main>
    </div>

    <!-- Standard Back-Office/Admin Layout -->
    <div v-else class="h-screen bg-slate-50 flex overflow-hidden font-sans">
        
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 shadow-2xl relative z-20 transition-all duration-300">
            <!-- Brand -->
            <div class="h-20 flex items-center px-6 border-b border-white/5 bg-slate-900/50 backdrop-blur-sm">
                <div class="flex items-center gap-3 text-white">
                    <div class="bg-gradient-to-tr from-indigo-500 to-teal-400 p-2 rounded-xl shadow-lg shadow-indigo-500/20 transform -rotate-6">
                        <svg class="w-5 h-5 transform rotate-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <span class="text-xl font-bold tracking-wide">Cura<span class="text-indigo-400">Sys</span></span>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1.5 custom-scrollbar">
                
                <router-link :to="{ name: 'Dashboard' }" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white" active-class="bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </router-link>

                <div class="pt-4 pb-1">
                    <p class="px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Modules</p>
                </div>

                <router-link :to="{ name: 'POS' }" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white" active-class="bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Pharmacy POS
                </router-link>
                
                <router-link :to="{ name: 'Pharmacy' }" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white" active-class="bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Medicine Catalog
                </router-link>

                <router-link :to="{ name: 'Medical Center' }" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white" active-class="bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Medical Center
                </router-link>

                <router-link :to="{ name: 'Customers' }" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white" active-class="bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Customers
                </router-link>

                <router-link :to="{ name: 'Purchasing' }" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white" active-class="bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                    Purchasing / GRN
                </router-link>

                <div class="pt-4 pb-1">
                    <p class="px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Settings</p>
                </div>

                <router-link :to="{ name: 'Administration' }" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-slate-800 hover:text-white" active-class="bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Administration
                </router-link>

            </nav>

            <!-- User Profile Bottom -->
            <div class="p-4 border-t border-white/5 bg-slate-900">
                <button @click="logout" class="flex items-center w-full p-3 rounded-xl hover:bg-slate-800 transition-colors">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'Admin User')}&background=6366f1&color=fff`" class="w-9 h-9 rounded-full ring-2 ring-slate-800 hover:ring-indigo-500 transition-all">
                    <div class="ml-3 text-left">
                        <p class="text-sm font-semibold text-white">{{ user.name || 'Admin User' }}</p>
                        <p class="text-xs text-slate-400 hover:text-red-400 transition-colors">Sign Out</p>
                    </div>
                </button>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative z-10">
            <!-- Topbar -->
            <header class="h-16 bg-white/70 backdrop-blur-xl border-b border-slate-200/50 flex items-center justify-between px-6 shrink-0">
                <div class="flex items-center gap-4 flex-1">
                    <div class="relative w-64">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" placeholder="Search..." class="w-full bg-slate-100/50 border-none rounded-lg pl-9 pr-4 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500/50 transition-shadow">
                    </div>
                    
                    <!-- Network Status -->
                    <div v-if="!isOnline" class="flex items-center gap-2 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">
                        <div class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></div>
                        Offline
                    </div>
                    <div v-else-if="pendingSalesCount > 0" class="flex items-center gap-2 bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-semibold cursor-pointer hover:bg-indigo-200 transition-colors" @click="triggerManualSync">
                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Syncing {{ pendingSalesCount }} Sales...
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors rounded-full hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                </div>
            </header>

            <!-- Page Content (Router View) -->
            <main class="flex-1 relative overflow-y-auto bg-slate-50/50">
                <router-view v-slot="{ Component }">
                    <transition name="fade" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </main>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { syncPendingSales, getPendingSales } from '../services/OfflineService';
import axios from 'axios';

const router = useRouter();
const isOnline = ref(navigator.onLine);
const pendingSalesCount = ref(0);
const backendPendingCount = ref(0);
const user = ref(JSON.parse(localStorage.getItem('auth_user') || '{}'));

const isCashier = computed(() => {
    return (user.value.roles || []).some(r => r.name === 'Cashier');
});

const isSalesPerson = computed(() => {
    return (user.value.roles || []).some(r => r.name === 'Sales Person');
});

const showCashMovementModal = ref(false);
const movementForm = reactive({
    mode: 'out', // 'in' or 'out'
    subcategory: 'Safe Drop (Deposit to Vault)',
    amount: '',
    reason: ''
});

const showMovementSubDropdown = ref(false);
const filteredMovementSubs = ref([]);
const dbMovementSubs = ref([]);

const activeSession = ref(null);
const drawerBalance = ref(0.00);

// Open Float controls
const showOpenSessionModal = ref(false);
const suggestedFloat = ref(150.00);
const openSessionForm = reactive({
    opening_amount: '',
    reason: ''
});

const loadMovementSubcategories = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/session/movement-subcategories');
        dbMovementSubs.value = res.data.data;
        filterMovementSubs();
    } catch(e) {
        console.error("Failed to load movement subcategories", e);
    }
};

const filterMovementSubs = () => {
    const presets = movementForm.mode === 'in' 
        ? ['Float Add (Supply Change)', 'Extra Cash Deposit']
        : ['Safe Drop (Deposit to Vault)', 'Supplier Payout', 'Local Store Expense', 'Customer Refund'];
    
    const allSubs = Array.from(new Set([...presets, ...dbMovementSubs.value]));
    const query = movementForm.subcategory ? movementForm.subcategory.toLowerCase() : '';
    filteredMovementSubs.value = allSubs.filter(sub => sub.toLowerCase().includes(query));
};

const selectMovementSub = (sub) => {
    movementForm.subcategory = sub;
    showMovementSubDropdown.value = false;
};

const setMovementMode = (mode) => {
    movementForm.mode = mode;
    movementForm.subcategory = mode === 'in' ? 'Float Add (Supply Change)' : 'Safe Drop (Deposit to Vault)';
    filterMovementSubs();
};

const expectedNewBalance = computed(() => {
    const amt = parseFloat(movementForm.amount || 0);
    if (movementForm.mode === 'in') {
        return drawerBalance.value + amt;
    } else {
        return drawerBalance.value - amt;
    }
});

const fetchSuggestedFloat = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/session/suggest-float');
        suggestedFloat.value = parseFloat(res.data.suggested_amount || 150.00);
        openSessionForm.opening_amount = suggestedFloat.value; // pre-populate suggestion!
        
        // Show modal if cashier has not skipped
        const skipped = localStorage.getItem('skipped_float_prompt');
        if (!skipped) {
            showOpenSessionModal.value = true;
        }
    } catch(e) {
        console.error(e);
    }
};

const skipFloatPrompt = () => {
    showOpenSessionModal.value = false;
    localStorage.setItem('skipped_float_prompt', 'true');
};

const submitOpenSession = async () => {
    if (!openSessionForm.opening_amount) {
        alert("Please enter an opening float amount.");
        return;
    }

    try {
        await axios.post('/api/v1/pharmacy/pos/session/open', {
            register_id: 1, // default register
            opening_amount: parseFloat(openSessionForm.opening_amount),
            reason: 'Opening register float'
        });
        
        alert("Register session opened successfully!");
        showOpenSessionModal.value = false;
        localStorage.removeItem('skipped_float_prompt');
        await loadActiveSession();
        // Refresh cashier list page if active
        if (router.currentRoute.value.name === 'Cashier') {
            window.location.reload();
        }
    } catch(e) {
        alert(e.response?.data?.message || "Failed to open register session");
    }
};

const loadActiveSession = async () => {
    try {
        const res = await axios.get('/api/v1/pharmacy/pos/session/active');
        activeSession.value = res.data.session;
        drawerBalance.value = parseFloat(res.data.balance || 0);
        
        // If Cashier and session is missing, fetch suggested float
        if (isCashier.value && !activeSession.value) {
            await fetchSuggestedFloat();
        }
    } catch(e) {
        console.error("Failed to load active session details", e);
    }
};

const openDrawerModal = async () => {
    await loadActiveSession();
    movementForm.mode = 'out';
    movementForm.subcategory = 'Safe Drop (Deposit to Vault)';
    movementForm.amount = '';
    movementForm.reason = '';
    await loadMovementSubcategories();
    showCashMovementModal.value = true;
};

const submitCashMovement = async () => {
    if (!movementForm.amount || !movementForm.reason || !movementForm.subcategory) {
        alert('Please fill out amount, reason, and sub-category details.');
        return;
    }
    if (!activeSession.value) {
        alert('No active register shift session found.');
        return;
    }
    if (movementForm.mode === 'out' && parseFloat(movementForm.amount) > drawerBalance.value) {
        alert(`Insufficient cash in drawer. Current balance is $${drawerBalance.value.toFixed(2)}.`);
        return;
    }

    // Determine type for backend
    let backendType = 'payout';
    if (movementForm.mode === 'in') {
        backendType = 'float_add';
    } else if (movementForm.subcategory.toLowerCase().includes('drop')) {
        backendType = 'drop';
    } else {
        backendType = 'payout';
    }

    try {
        await axios.post('/api/v1/pharmacy/pos/session/movement', {
            session_id: activeSession.value.id,
            type: backendType,
            subcategory: movementForm.subcategory,
            amount: parseFloat(movementForm.amount),
            reason: `${movementForm.subcategory}: ${movementForm.reason}`
        });
        alert('Cash drawer transaction logged successfully.');
        showCashMovementModal.value = false;
        movementForm.amount = '';
        movementForm.reason = '';
        await loadActiveSession(); // refresh balance
        
        // Refresh cashier list page if active to update sidebar stats
        if (router.currentRoute.value.name === 'Cashier') {
            window.location.reload();
        }
    } catch(e) {
        alert(e.response?.data?.message || 'Failed to log cash movement.');
    }
};

let syncInterval = null;

const checkPendingSales = async () => {
    try {
        const sales = await getPendingSales();
        pendingSalesCount.value = sales.length;

        if (isCashier.value) {
            const res = await axios.get('/api/v1/pharmacy/pos/pending');
            backendPendingCount.value = res.data.data.length;
        }
    } catch (e) {
        console.error("Failed to count pending sales", e);
    }
};

const triggerManualSync = async () => {
    if (!isOnline.value) return;
    try {
        await syncPendingSales();
        await checkPendingSales();
    } catch (e) {
        console.error("Manual sync failed", e);
    }
};

const handleOnline = async () => {
    isOnline.value = true;
    await triggerManualSync();
};

const handleOffline = () => {
    isOnline.value = false;
};

onMounted(() => {
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
    checkPendingSales();
    if (isCashier.value) {
        loadActiveSession();
    }

    // Close dropdown on click outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) {
            showMovementSubDropdown.value = false;
        }
    });
    
    // Background sync loop (every 30s)
    syncInterval = setInterval(() => {
        if (isOnline.value && pendingSalesCount.value > 0) {
            triggerManualSync();
        } else {
            checkPendingSales();
        }
    }, 30000);
});

onUnmounted(() => {
    window.removeEventListener('online', handleOnline);
    window.removeEventListener('offline', handleOffline);
    clearInterval(syncInterval);
});

const logout = () => {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
    router.push({ name: 'Login' });
};
</script>

<style>
/* Page Transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
}
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Custom Scrollbar for sidebar */
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
