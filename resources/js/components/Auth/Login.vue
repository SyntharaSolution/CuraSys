<template>
    <div class="min-h-screen bg-[#0f172a] font-sans flex flex-col justify-center items-center p-6 relative overflow-hidden">
        
        <!-- Database Connection Status Badge -->
        <div class="absolute top-6 right-6 flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold backdrop-blur-md border shadow-lg z-50 transition-all duration-300"
            :class="{
                'bg-slate-800/80 text-slate-300 border-slate-700': dbStatus === 'checking',
                'bg-emerald-500/20 text-emerald-400 border-emerald-500/30': dbStatus === 'connected',
                'bg-red-500/20 text-red-400 border-red-500/30': dbStatus === 'disconnected'
            }"
        >
            <div class="w-2 h-2 rounded-full" 
                :class="{
                    'bg-slate-400 animate-pulse': dbStatus === 'checking',
                    'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]': dbStatus === 'connected',
                    'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)]': dbStatus === 'disconnected'
                }"
            ></div>
            <span>
                {{ dbStatus === 'checking' ? 'Checking DB...' : (dbStatus === 'connected' ? 'DB Connected' : 'DB Disconnected') }}
            </span>
        </div>

        <!-- Ambient Background Effects -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[30%] -right-[10%] w-[70%] h-[70%] rounded-full bg-indigo-500/20 blur-[120px]"></div>
            <div class="absolute -bottom-[30%] -left-[10%] w-[70%] h-[70%] rounded-full bg-teal-500/20 blur-[120px]"></div>
        </div>

        <div class="relative sm:max-w-md sm:w-full sm:mx-auto">
            <!-- Brand Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-teal-500 text-white shadow-xl shadow-indigo-500/30 mb-6 transform -rotate-6">
                    <svg class="w-8 h-8 transform rotate-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h1 class="text-4xl font-extrabold text-white tracking-tight">Cura<span class="text-indigo-400">Sys</span></h1>
                <p class="text-slate-400 mt-2 text-sm font-medium tracking-wide uppercase">Unified Healthcare ERP</p>
            </div>

            <!-- Login Mode Toggle -->
            <div class="flex items-center justify-center gap-1 mb-6 bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-1">
                <button 
                    @click="loginMode = 'admin'"
                    :class="['flex-1 py-2.5 px-4 rounded-xl text-xs font-bold uppercase tracking-wider transition-all', loginMode === 'admin' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400 hover:text-slate-200']"
                >
                    Admin / Cashier
                </button>
                <button 
                    @click="loginMode = 'salesperson'"
                    :class="['flex-1 py-2.5 px-4 rounded-xl text-xs font-bold uppercase tracking-wider transition-all', loginMode === 'salesperson' ? 'bg-teal-600 text-white shadow-lg shadow-teal-500/30' : 'text-slate-400 hover:text-slate-200']"
                >
                    Sales Person
                </button>
            </div>

            <!-- Admin/Cashier Login Card -->
            <div v-if="loginMode === 'admin'" class="bg-white/10 backdrop-blur-xl border border-white/10 p-8 sm:p-10 shadow-2xl rounded-3xl relative overflow-hidden">
                <!-- Highlight line -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-teal-500"></div>

                <form @submit.prevent="login" class="space-y-6">
                    
                    <div v-if="error" class="bg-red-500/10 border border-red-500/50 text-red-400 text-sm p-4 rounded-xl flex items-center gap-3 backdrop-blur-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p>{{ error }}</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                            </div>
                            <input 
                                id="email" 
                                type="email" 
                                v-model="form.email" 
                                required
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors sm:text-sm"
                                placeholder="you@example.com"
                            >
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                            <a href="#" class="text-xs font-medium text-indigo-400 hover:text-indigo-300 transition-colors">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input 
                                id="password" 
                                type="password" 
                                v-model="form.password" 
                                required
                                class="block w-full pl-11 pr-4 py-3.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors sm:text-sm"
                                placeholder="••••••••"
                            >
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-700 rounded bg-slate-900/50">
                        <label for="remember-me" class="ml-2 block text-sm text-slate-400">
                            Remember me
                        </label>
                    </div>

                    <div>
                        <button 
                            type="submit" 
                            :disabled="loading"
                            class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-indigo-500 transition-all shadow-[0_0_20px_rgba(79,70,229,0.3)] hover:shadow-[0_0_25px_rgba(79,70,229,0.5)] disabled:opacity-70"
                        >
                            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="animate-spin h-5 w-5 text-indigo-300" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            {{ loading ? 'Authenticating...' : 'Sign in to workspace' }}
                            
                            <span v-if="!loading" class="absolute right-0 inset-y-0 flex items-center pr-4 transform translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all">
                                <svg class="w-5 h-5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sales Person Code Login Card -->
            <div v-else class="bg-white/10 backdrop-blur-xl border border-white/10 p-8 sm:p-10 shadow-2xl rounded-3xl relative overflow-hidden">
                <!-- Highlight line -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-teal-500 via-emerald-500 to-cyan-500"></div>

                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-500/20 border border-teal-500/30 text-teal-400 mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold text-white">Sales Person Quick Login</h2>
                    <p class="text-xs text-slate-400 mt-1">Enter your assigned login code to access the terminal.</p>
                </div>

                <form @submit.prevent="loginByCode" class="space-y-5">
                    <div v-if="error" class="bg-red-500/10 border border-red-500/50 text-red-400 text-sm p-4 rounded-xl flex items-center gap-3 backdrop-blur-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p>{{ error }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2 text-center">Login Code</label>
                        <input 
                            type="text" 
                            v-model="codeForm.login_code"
                            inputmode="numeric"
                            maxlength="10"
                            required
                            class="block w-full px-4 py-4 bg-slate-900/50 border border-slate-700/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-colors text-center text-2xl font-extrabold tracking-[0.5em]"
                            placeholder="• • • •"
                            autocomplete="off"
                        >
                    </div>

                    <!-- Quick Numpad -->
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            v-for="n in [1,2,3,4,5,6,7,8,9]" 
                            :key="n"
                            type="button"
                            @click="appendCode(n)"
                            class="py-3.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-white text-lg font-bold transition-all active:scale-95"
                        >
                            {{ n }}
                        </button>
                        <button 
                            type="button"
                            @click="clearCode"
                            class="py-3.5 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 rounded-xl text-red-400 text-sm font-bold transition-all active:scale-95"
                        >
                            CLR
                        </button>
                        <button 
                            type="button"
                            @click="appendCode(0)"
                            class="py-3.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-white text-lg font-bold transition-all active:scale-95"
                        >
                            0
                        </button>
                        <button 
                            type="button"
                            @click="backspaceCode"
                            class="py-3.5 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 rounded-xl text-amber-400 text-sm font-bold transition-all active:scale-95"
                        >
                            ⌫
                        </button>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="loading || !codeForm.login_code"
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-teal-600 hover:bg-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-teal-500 transition-all shadow-[0_0_20px_rgba(20,184,166,0.3)] hover:shadow-[0_0_25px_rgba(20,184,166,0.5)] disabled:opacity-50"
                    >
                        <span v-if="loading" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-teal-300" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verifying...
                        </span>
                        <span v-else>Login to Sales Terminal</span>
                    </button>
                </form>
            </div>
            
            <p class="mt-8 text-center text-xs text-slate-500">
                &copy; {{ new Date().getFullYear() }} CuraSys Enterprise. All rights reserved.
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const loading = ref(false);
const error = ref('');
const dbStatus = ref('checking'); // checking, connected, disconnected
const loginMode = ref('admin'); // 'admin' or 'salesperson'

const form = reactive({
    email: '',
    password: ''
});

const codeForm = reactive({
    login_code: ''
});

const appendCode = (n) => {
    if (codeForm.login_code.length < 10) {
        codeForm.login_code += String(n);
    }
};

const clearCode = () => {
    codeForm.login_code = '';
};

const backspaceCode = () => {
    codeForm.login_code = codeForm.login_code.slice(0, -1);
};

const checkDbConnection = async () => {
    try {
        const response = await axios.get('/api/health/db');
        if (response.data.status === 'connected') {
            dbStatus.value = 'connected';
        } else {
            dbStatus.value = 'disconnected';
        }
    } catch (e) {
        dbStatus.value = 'disconnected';
    }
};

onMounted(() => {
    checkDbConnection();
});

const login = async () => {
    loading.value = true;
    error.value = '';

    try {
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post('/api/v1/auth/login', form);
        
        if (response.data.access_token) {
            localStorage.setItem('auth_token', response.data.access_token);
            localStorage.setItem('auth_user', JSON.stringify(response.data.user));
            axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;
            
            const roles = response.data.user?.roles || [];
            const isCashier = roles.some(r => r.name === 'Cashier');
            if (isCashier) {
                router.push({ name: 'Cashier' });
            } else {
                router.push({ name: 'Dashboard' });
            }
        }

    } catch (e) {
        if (e.response && e.response.status === 422) {
            error.value = 'Invalid email or password provided.';
        } else {
            error.value = 'An error occurred during authentication. Please try again.';
        }
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const loginByCode = async () => {
    loading.value = true;
    error.value = '';

    try {
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post('/api/v1/auth/login-by-code', codeForm);
        
        if (response.data.access_token) {
            localStorage.setItem('auth_token', response.data.access_token);
            localStorage.setItem('auth_user', JSON.stringify(response.data.user));
            axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;
            
            // Sales Person always goes to POS Terminal
            router.push({ name: 'POS Terminal' });
        }

    } catch (e) {
        if (e.response && e.response.status === 401) {
            error.value = 'Invalid login code. Please try again.';
        } else if (e.response && e.response.status === 403) {
            error.value = e.response.data.message || 'Access denied.';
        } else {
            error.value = 'Login failed. Please try again.';
        }
        console.error(e);
    } finally {
        loading.value = false;
        codeForm.login_code = '';
    }
};
</script>
